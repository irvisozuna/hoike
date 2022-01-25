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
                }else{
                    $data_return = array_merge($data_return,transformObjectToString(transformBlock($value,$key)));
                }
                break;
            case 'object':
                $data_return = array_merge($data_return,transformProperty($value,$key));
                break;
            case 'string':
                $is_image = strpos($key, 'image_');
                if($is_image !== false){
                    $value = isValidImage($value);
                }
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
function saveFile($data,$type = 'base64'){
    $path = storage_path('app/img/tmp');
    $imgdata = base64_decode($data);
    $mimetype = getImageMimeType($imgdata);
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    $file = $path.'/'.microtime().'.'.$mimetype;
    file_put_contents($file, base64_decode($data));
    return $file;
}

function getBytesFromHexString($hexdata)
{
  for($count = 0; $count < strlen($hexdata); $count+=2)
    $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));

  return implode($bytes);
}

function getImageMimeType($imagedata)
{
  $imagemimetypes = array( 
    "jpeg" => "FFD8", 
    "png" => "89504E470D0A1A0A", 
    "gif" => "474946",
    "bmp" => "424D", 
    "tiff" => "4949",
    "tiff" => "4D4D"
  );

  foreach ($imagemimetypes as $mime => $hexbytes)
  {
    $bytes = getBytesFromHexString($hexbytes);
    if (substr($imagedata, 0, strlen($bytes)) == $bytes)
      return $mime;
  }

  return "png";
}

function isValidImage($data){
    if (base64_encode(base64_decode($data, true)) === $data){
        return saveFile($data);
    }
    $re = '/^[^?]*\.(jpg|jpeg|gif|png)/m';
    preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);
    if(count($matches)> 0){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  
        $image = file_get_contents($data,false, stream_context_create($arrContextOptions));
        return saveFile(base64_encode($image));
    }
    return '';
}