<?php


class Templator
{
    private $templateContent;
    
    /**
     * Load a template file into memory.
     * @param string $fileName Path to the template file to be loaded.
     */
    public function loadTemplate(string $fileName)
    {

        if (!file_exists($fileName)){
            throw new Exception();
        }
        $content = file_get_contents($fileName);
        if ($content === false or empty($content)) {
            throw new Exception();
            
        }
        $this->templateContent = $content;
    }

    /**
     * Compile the loaded template (transpill it into interleaved-PHP) and save the result in a file.
     * @param string $fileName Path where the result should be saved.
     */
    public function compileAndSave(string $fileName)
    {
        if ($this->templateContent === null) {
            throw new Exception();    
        }
        
        $compiledContent = $this->compileTemplate($this->templateContent);

        if (@file_put_contents($fileName, $compiledContent) === false){
        throw new Exception();
        
        }
    }

    private function compileTemplate(string $content)
{
    $compiledContent = '';
    $stack = [];

    while ($content !== '') {
        $openPos = strpos($content, '{');
        
        if ($openPos === false) {
            $compiledContent .= $content;
            break;
        }

        $compiledContent .= substr($content, 0, $openPos);
        $content = substr($content, $openPos + 1); // Move the pointer after {

        $closePos = strpos($content, '}');

        if ($closePos === false) {
            throw new Exception();
        }

        $marker = substr($content, 0, $closePos);
        $content = substr($content, $closePos + 1);

        if (strncmp($marker, '= ', 2) === 0) {
            $compiledContent .= '<?= htmlspecialchars(' . trim(substr($marker, 2)) . ') ?>';
        } 
        elseif (strncmp($marker, 'if ', 3) === 0) {
            $compiledContent .= '<?php if (' . trim(substr($marker, 3)) . ') { ?>';
            array_push($stack, 'if');
        } 
        elseif (strncmp($marker, '/if', 4) === 0) {
            if (empty($stack) || array_pop($stack) !== 'if') {
                throw new Exception();
            }
            $compiledContent .= '<?php } ?>';
        } 
        elseif (strncmp($marker, 'for ', 4) === 0) {
            $compiledContent .= '<?php for (' . trim(substr($marker, 4)) . ') { ?>';
            array_push($stack, 'for');
        } 
        elseif (strncmp($marker, '/for', 5) === 0) {
            if (empty($stack) || array_pop($stack) !== 'for') {
                throw new Exception();
            }
            $compiledContent .= '<?php } ?>';
        } 
        elseif (strncmp($marker, 'foreach ', 8) === 0) {
            $compiledContent .= '<?php foreach (' . trim(substr($marker, 8)) . ') { ?>';
            array_push($stack, 'foreach');
        } 
        elseif (strncmp($marker, '/foreach', 9) === 0) {
            if (empty($stack) || array_pop($stack) !== 'foreach') {
                throw new Exception();
            }
            $compiledContent .= '<?php } ?>';
        } 
        else {
            $compiledContent .= '{' . $marker . '}';
        }
    }

    if (!empty($stack)) {
        throw new Exception();
    }

    return $compiledContent;
}
}
