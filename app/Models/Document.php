<?php

namespace App\Models;

use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\TemplateProcessor;

class Document
{
    protected $name = "hoike";
    protected $layout;
    protected $parameters;
    protected $data;
    private   $_path_file_generate;
    private   $_path_file_tmp;
    private   $extension = 'docx';

    public function __construct($layout)
    {
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
        $data = transformObjectToString($data);
        $this->data = $data;
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
        foreach($this->data as $key => $value){
            if(is_array($value)){
                foreach ($value as $k => $v) {
                    $value[$k]->{$key} = '';
                }
                $this->layout->cloneRowAndSetValues($key, $value);
                unset($this->data[$key]);
            }
        }
        $this->layout->setValues($this->data);
    }
}
