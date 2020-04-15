<?php
function transformObjectToString($data){

    $data_return = [];
    foreach ($data as $key => $value) {
        
        switch (gettype($value)) {
            case 'array':
                $is_block = strpos($key, 'block_');
                if($is_block !== false){
                    $after_key = str_replace("block_", "", $key);
                    $data_return = array_merge($data_return,transformObjectToString(transformBlock($value,$after_key)));
                }
                break;
            case 'object':
                $data_return = array_merge($data_return,transformProperty($value,$key));
                break;
            case 'string':
                $data_return[$key] = $value;
                break;
            default:
                
                break;
        }
    }
    $data_return = array_merge($data_return,convertToArray($data_return));

    foreach ($data_return as $key => $value) {
       if(gettype($value) != 'string' && gettype($value) != 'integer'){
            $data_return = array_merge($data_return,transformObjectToString($data_return));
            unset($data_return[$key]);
       }
    }
    return $data_return;
}
function transformProperty($data,$parent){
    $data_return = [];
    $properties = get_object_vars($data);
    foreach($properties as $key => $value){
        $data_return[$parent.'.'.$key] = $value;
    }
    return $data_return;
}
function transformBlock($data,$parent){
    $data_return = [];
    foreach ($data as $key => $value) {
        $data_return[$parent.$key] = $value;
    }
    return $data_return;
}
function convertToArray($data){
    $return_data = [];
    foreach ($data as $key => $value) {
        if(is_array($value)){
            for ($i=0; $i < count($value); $i++) {
                $employe = (array)$value[$i];
                foreach ($employe as $k => $v) {
                    $return_data[$key.'.'.$k.'#'.($i+1)] = $v;
                }
            }
            
        }
    }

    return $return_data;
}

// 14 => "plants0.employees.employe_id#1"
//   15 => "plants0.employees.employe_name#1"
//   16 => "plants0.employees.reg_hrs#1"
//   17 => "plants0.employees.reg_pay#1"
//   18 => "plants0.employees.ot_hrs#1"
//   19 => "plants0.employees.ot_pay#1"