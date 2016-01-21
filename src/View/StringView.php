<?php 
namespace Appzcoder\CrudGenerator\View;

class StringView extends \Illuminate\View\View {
    protected $contents;
    
    /**
     * Put the passed blade template string in the contents
     * 
     * @param \Illuminate\View\Factory $factory
     * @param \Illuminate\View\Engines\EngineInterface $engine
     * @param string $contents
     * @param array $data
     */
    public function __construct(\Illuminate\View\Factory $factory, \Illuminate\View\Engines\EngineInterface $engine, $contents, $data = array()) {
        // $path and $view left out, those are only used for the engine to get 
        // the contents, which we'll be overriding
        $view = null;
        $path = null;
        
        $this->setContents($contents);
        
        parent::__construct($factory, $engine, $view, $path, $data);
    }
    
    /**
     * Contents are set straight from the constructor
     * @param string $contents
     */
    public function setContents($contents) {
        $this->contents = $contents;
    }
    
    /**
     * This actually overrides Illuminate\View\View::getContents()
     * To get the complier from the engine and run compileString() on it
     * 
     * @return string
     * @throws \Exception
     */
    protected function getContents() {
        if(!method_exists($this->engine, 'getCompiler')) {
            throw new \Exception('Method getCompiler not available on engine (is this PhpEngine?)');
        }
        
        // We'll need the compiler
        $compiler = $this->engine->getCompiler();
        
        if(!method_exists($compiler, 'compileString')) {
            throw new \Exception('Compiler needs to implement compileString');
        }
        
        // now compile the string in the contents property
        $compiled = $compiler->compileString($this->contents);
        
        $evaluated = $this->evaluateContent($compiled, $this->gatherData());
        
        return $evaluated;
    }
    
    /**
     * This allows for PHP in the template
     * 
     * @param string $__contents The template after blade has parsed it
     * @param array $__data
     * @return string
     */
    protected function evaluateContent($__contents, $__data)
    {
        $obLevel = ob_get_level();

        ob_start();

        extract($__data);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try
        {
            eval("\r\n?>".$__contents."<?php\r\n");
        }
        catch (Exception $e)
        {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }
}