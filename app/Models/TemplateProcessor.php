<?php
namespace App\Models;

use PhpOffice\PhpWord\TemplateProcessor as PhpWordTemplateProcessor;

class TemplateProcessor extends PhpWordTemplateProcessor
{
    public function cloneBlockWithTable($blockname, $clones = 1, $replace = true, $indexVariables = false, $variableReplacements = null)
    {
        $xmlBlock = null;
        $matches = array();
        preg_match(
            '/(<w:p\b.*>\${' . $blockname . '}<\/w:.*?p>)(.*)(<w:p\b.*\${\/' . $blockname . '}<\/w:.*?p>)/is',
            $this->tempDocumentMainPart,
            $matches
        );
        if (isset($matches[2])) {
            $xmlBlock = $matches[2];
            if ($indexVariables) {
                $cloned = $this->indexClonedVariables($clones, $xmlBlock);
            } elseif ($variableReplacements !== null && is_array($variableReplacements)) {
                $cloned = $this->replaceClonedVariables($variableReplacements, $xmlBlock);
            } else {
                $cloned = array();
                for ($i = 1; $i <= $clones; $i++) {
                    $cloned[] = $xmlBlock;
                }
            }
            
            for ($i=0; $i < count($cloned); $i++) { 
                $cloned[$i] = $this->replaceIterator($cloned[$i],$i);
            }

            if ($replace) {
                $this->tempDocumentMainPart = str_replace(
                    $matches[1] . $matches[2] . $matches[3],
                    implode('', $cloned),
                    $this->tempDocumentMainPart
                );
            }
        }
        return $xmlBlock;
    }
    /**
     * Set values from a one-dimensional array of "variable => value"-pairs.
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        foreach ($values as $macro => $replace) {
            $is_image = strpos($macro, 'image_');
            if($is_image !== false && $replace !== ''){
                $this->setImageValue($macro, $replace);
            }else{
                $this->setValue($macro, $replace);
            }
        }
    }
    private function replaceIterator($xmlBlock,$iterator){
        $pattern = '/\[([a-zA-Z])\]/';
        $replace = "$iterator";
        return preg_replace($pattern, $replace, $xmlBlock);
    }    
}
