<?php 
namespace Appzcoder\CrudGenerator\View;

class Factory extends \Illuminate\View\Factory {

    /**
    * Get the evaluated view contents for the given string.
    *
    * @param  string  $view
    * @param  array   $data
    * @param  array   $mergeData
    * @return \Illuminate\View\View
    */
    public function makeFromBladeString($viewString, $data = array(), $mergeData = array())
    {
        $data = array_merge($mergeData, $this->parseData($data));

        $this->callCreator($view = new StringView($this, $this->engines->resolve('blade'), $viewString, $data));

        return $view;
    }
}