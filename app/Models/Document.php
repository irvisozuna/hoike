<?php

namespace App\Models;


use App\Models\TemplateProcessor;

class Document
{
    protected $name = "hoike";
    protected $layout;
    protected $parameters;
    protected $data;
    protected $original_data;
    private   $_path_file_generate;
    private   $_path_file_tmp;
    private   $extension = 'docx';

    public function __construct($layout)
    {
        ini_set('max_execution_time', 3000);
        $this->layout = new TemplateProcessor($layout);
        $this->parameters = $this->layout->getVariables();
        $this->_path_file_generate    = storage_path('app/generate/');
        $this->_path_file_tmp         = storage_path('app/tmp/');
    }

    public function getParams(){
        return $this->layout->getVariables();
    }

    public function setData(array $data){
        $data = json_decode(json_encode($data), FALSE);
        
        $this->original_data = $data;
        $this->data = transformObjectToString($data);
        $this->proccessParameters();
    }
    public function generate(){
        $this->layout->saveAs($this->getPathFileTmp());
    }
    public function export($name = null,$convert_to = 'pdf'){
        if(is_null($name)){
            $name = time();
        }
        $converter = new OfficeConverter($this->getPathFileTmp(),$this->getPathFileGenerate());
        $file_exported = $converter->convertTo($name.'.'.$convert_to);
        
        return $file_exported;
    }
    public function getPathFileGenerate(){
        return $this->_path_file_generate;
    }
    public function getPathFileTmp(){
        return $this->_path_file_tmp.$this->name.'.'.$this->extension;
    }
    private function proccessParameters(){
       
        $this->generateParametersBlock();
        foreach ($this->layout->getVariables() as $key => $value) {
           if(!isset($this->data[$value])){
            $this->data[$value] = '';
           }
        }
        $this->layout->setValues($this->data);
    }
    private function generateParametersBlock(){
        foreach($this->original_data as $key => $value){
            $is_block = strpos($key, 'block_');
            if($is_block !== false){
                $this->layout->cloneBlockWithTable($key,count($value));
                $after_key = str_replace("block_", "", $key);
                $this->generateParametersRow($value,$after_key);
            }elseif(is_array($value)){
                foreach ($value as $k => $v) {
                    $value[$k]->{$key} = '';
                }
                $this->layout->cloneRowAndSetValues($key, $value);
                unset($this->data[$key]);
            }
        }
    }
    private function generateParametersRow($data,$after_key = ''){
        foreach($data as $key => $value){
            foreach ($value as $k => $v) {
                if(gettype($v) == 'array'){
                    $this->layout->cloneRow($after_key.$key.'.'.$k, count($v));
                }
            }
        }
    }
}
