<?php

    $file = (isset($argv[1]) ? $argv[1] : false);
    $variables_file = (isset($argv[2]) ? $argv[2] : false);

    $pattern = "#\+\+([^\s\+]+) = (((?!\+\+).)+)\+\+#";
    $html_replace_to = '=\1';

    if($file === false || $variables_file === false){
        echo "example: php test.pug ../variables.js";
    }

    $text = file_get_contents($file);
    file_put_contents($file . '.back',$text);
    
    preg_match_all($pattern,$text,$ret);

    $variables = [];

        foreach($ret[1] as $id=>$name){
            $variables[$name] = $ret[2][$id];
        }
    
    $text = preg_replace($pattern,$html_replace_to,$text);
   
    $lang_text = "\n// add from script $file \n";

    $ext_variables = [];

    foreach($variables as $name=>$value){
        if(strpos($name,'.') !== false){
            $ext_var = explode('.',$name);
            if(!isset($ext_variables[$ext_var[0]]))
                $ext_variables[$ext_var[0]] = [];
            $ext_variables[$ext_var[0]][$ext_var[1]] = $value;
        }else{
            $lang_text.='var '. $name . ' = "' . str_replace('"','\"',$value) . '"' . ";\n";
        }
    }

    if(!empty($ext_variables)){
        foreach($ext_variables as $first => $first_array){
            $lang_text.="\nvar ". $first . ' = {' . "\n";
            foreach($first_array as $second => $value){
                $lang_text.= '"' . $second . '":"' . str_replace('"','\"',$value) . '",' . "\n";
            }

            $lang_text.="};\n";
        }
    }


    $lang_text.="\nvar add = {\n";
    foreach($variables as $name => $value){
        $lang_text.= '"' . $name . '": ' . $name . ',' . "\n";
        
    }


    foreach($ext_variables as $first=>$trash){
        $lang_text.= '"' . $first . '": ' . $first . ',' . "\n";
    }


    $lang_text.="}; \n";

    
    
    file_put_contents($variables_file,$lang_text,FILE_APPEND);
    file_put_contents($file,$text);
    print_r($variables);

