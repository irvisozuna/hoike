<?php
function transformObjectToString($data){
    $data_return = [];
    foreach ($data as $key => $value) {
        
        switch (gettype($value)) {
            case 'array':
                $data_return[$key] = $value;
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