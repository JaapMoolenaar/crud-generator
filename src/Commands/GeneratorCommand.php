<?php

namespace Appzcoder\CrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand as IlluminateGeneratorCommand;

abstract class GeneratorCommand extends IlluminateGeneratorCommand{
    
    protected function getViewFactory() {
        return \App::make('BladeStringViewFactory');
    }
    
    protected function makeFromBladeString($viewString, $data) {
        $viewString = $this->preMake($viewString);
        
        $viewString = $this->getViewFactory()->makeFromBladeString($viewString, $data);
        
        $viewString = $this->postMake($viewString);
        
        return $viewString;
    }
    
    protected function preMake($viewString) {
        return preg_replace('/^<\?php/i', 'PHPTAG', $viewString);
    }
    
    protected function postMake($viewString) {
        return preg_replace('/^PHPTAG/', '<?php', $viewString);
    }
    
}
