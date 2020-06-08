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
    public function cloneRowAndSetValuesArray($search, $values)
    {
        $this->cloneRowArray($search, count($values));

        
        foreach ($values as $rowKey => $rowData) {
            foreach ($rowData as $macro => $replace) {
                $this->setValue($macro, $replace);
            }
        }
    }
    private function replaceIterator($xmlBlock,$iterator){
        $pattern = '/\[([a-zA-Z])\]/';
        $replace = "$iterator";
        return preg_replace($pattern, $replace, $xmlBlock);
    }
    public function cloneRowArray($search, $numberOfClones)
    {
        $search = static::ensureMacroCompleted($search);

        $tagPos = strpos($this->tempDocumentMainPart, $search);
        if (!$tagPos) {
            throw new Exception('Can not clone row, template variable not found or variable contains markup.');
        }

        $rowStart = $this->findRowStart($tagPos);
        $rowEnd = $this->findRowEnd($tagPos);
        $xmlRow = $this->getSlice($rowStart, $rowEnd);

        // Check if there's a cell spanning multiple rows.
        if (preg_match('#<w:vMerge w:val="restart"/>#', $xmlRow)) {
            // $extraRowStart = $rowEnd;
            $extraRowEnd = $rowEnd;
            while (true) {
                $extraRowStart = $this->findRowStart($extraRowEnd + 1);
                $extraRowEnd = $this->findRowEnd($extraRowEnd + 1);

                // If extraRowEnd is lower then 7, there was no next row found.
                if ($extraRowEnd < 7) {
                    break;
                }

                // If tmpXmlRow doesn't contain continue, this row is no longer part of the spanned row.
                $tmpXmlRow = $this->getSlice($extraRowStart, $extraRowEnd);
                if (!preg_match('#<w:vMerge/>#', $tmpXmlRow) &&
                    !preg_match('#<w:vMerge w:val="continue"\s*/>#', $tmpXmlRow)) {
                    break;
                }
                // This row was a spanned row, update $rowEnd and search for the next row.
                $rowEnd = $extraRowEnd;
            }
            $xmlRow = $this->getSlice($rowStart, $rowEnd);
        }

        $result = $this->getSlice(0, $rowStart);
        
        $result .= implode($this->indexClonedVariablesArray($numberOfClones, $xmlRow));
        $result .= $this->getSlice($rowEnd);

        $this->tempDocumentMainPart = $result;
    }
    protected function indexClonedVariablesArray($count, $xmlBlock)
    {
        $results = array();
        for ($i = 0; $i < $count; $i++) {
            $pattern = '/\[([a-zA-Z])\]/';
            $replace = $i;
            $results[] = preg_replace($pattern, $replace, $xmlBlock);
            //$results[] = preg_replace('/\$\{(.*?)\}/', '\${\\1[' . $i . ']}', $xmlBlock);
        }

        return $results;
    }
}
