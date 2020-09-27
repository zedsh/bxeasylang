<?php

$file = (isset($argv[1]) ? $argv[1] : false);
$pattern = "#\+\+([^\s\+]+) = (((?!\+\+).)+)\+\+#";
$html_replace_to = '{{$\1}}';

if ($file === false) {
    echo "example: php blade_separator.php tests/test.html\n";
    die;
}

$text = file_get_contents($file);
preg_match_all($pattern, $text, $ret);

$variables = [];

foreach ($ret[1] as $id => $name) {
    $variables[$name] = $ret[2][$id];
}

$text = preg_replace($pattern, $html_replace_to, $text);

$lang_text = "<?php \n";

$ext_variables = [];

foreach ($variables as $name => $value) {
    if (strpos($name, '->') !== false) {
        $ext_var = explode('->', $name);
        if (!isset($ext_variables[$ext_var[0]])) {
            $ext_variables[$ext_var[0]] = [];
        }
        $ext_variables[$ext_var[0]][$ext_var[1]] = $value;
    } else {
    }
}

if (!empty($ext_variables)) {
    foreach ($ext_variables as $first => $first_array) {
        $lang_text .= "\n$" . $first . ' = [' . "\n";
        foreach ($first_array as $second => $value) {
            $lang_text .= '"' . $second . '" => "' . str_replace('"', '\"', $value) . '",' . "\n";
        }

        $lang_text .= "];\n";
    }
}

$lang_text .= "\n\$add = [\n";
foreach ($variables as $name => $value) {
    if(strpos($name,'->') === false) {
        $lang_text .= '"' . $name . '" => "' . str_replace('"', '\"', $value) . '",' . "\n";
    }
}

foreach ($ext_variables as $first => $trash) {
    $lang_text .= '"' . $first . '" => $' . $first . ',' . "\n";
}

$lang_text .= "]; \n";


$pathInfo = pathinfo($file);
file_put_contents($pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_variables.php', $lang_text);
file_put_contents($pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_separated.' . $pathInfo['extension'], $text);
print_r($variables);

