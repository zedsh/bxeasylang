<?php

    $file = (isset($argv[1]) ? $argv[1] : false);
    $prefix = (isset($argv[2]) ? $argv[2] : false);
    $lang_ru_dir = dirname($file) . '/lang/ru/'; 
    $lang_ru = $lang_ru_dir . basename($file);

    $pattern = "#\+\+([^\s\+]+) = (((?!\+\+).)+)\+\+#";
    $html_replace_to = '<?= Loc::getMessage(\'' . $prefix . '_\1\') ?>';

    if($file === false || $prefix === false){
        echo "example: php test.php PREFIX\n";
        die;
    }

    $text = file_get_contents($file);
    file_put_contents($file . '.back',$text);
    
    preg_match_all($pattern,$text,$ret);

    $variables = [];

        foreach($ret[1] as $id=>$name){
            $variables[$name] = $ret[2][$id];
        }
    
    $text = preg_replace($pattern,$html_replace_to,$text);
   
    $lang_text = "\n// add from script \n";

    foreach($variables as $name=>$value){
        $lang_text.='$MESS["' . $prefix . "_" . $name . '"] = "' . str_replace('"','\"',$value) . '"' . ";\n";
    }
    
    `mkdir -p $lang_ru_dir`;
    file_put_contents($lang_ru,$lang_text,FILE_APPEND);
    file_put_contents($file,$text);
    print_r($variables);

