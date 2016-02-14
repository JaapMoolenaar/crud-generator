<?php

namespace Appzcoder\CrudGenerator\Commands;

class CrudControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:controller
                            {name : The name of the controler.}
                            {--crud-name= : The name of the Crud.}
                            {--model-name= : The name of the Model.}
                            {--view-path= : The name of the view path.}
                            {--fields= : fields config for validations.}
                            {--required-fields= : Required fields php string for validations.}
                            {--route-group= : Prefix of the route group.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudgenerator.custom_template')
        ? config('crudgenerator.path') . '/controller.stub'
        : __DIR__ . '/../stubs/controller.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers';
    }

    
    
    protected function parseName($name) {
        $name = preg_replace('/Controller$/i', '', $name).'Controller';
        
        return parent::parseName($name);
    }
    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($controllerName)
    {
        $stub = $this->files->get($this->getStub());

        $name = $this->argument('name');
        $modelName = str_singular($name);

        if ($this->option('fields')) {
            $fields = $this->option('fields');

            $fieldsArray = explode(',', $fields);
            $requiredFields = '';
            $requiredFieldsStr = '';

            foreach ($fieldsArray as $item) {
                $itemArray = explode(':', $item);
                $currentField = trim($itemArray[0]);
                $requiredFieldsStr .= (isset($itemArray[2])
                    && (trim($itemArray[2]) == 'req'
                        || trim($itemArray[2]) == 'required'))
                ? "'$currentField' => 'required', " : '';
            }

            $requiredFields = ($requiredFieldsStr != '') ? "[" . $requiredFieldsStr . "]" : '';
        }
        
        
        
        $viewPath = $this->option('view-path') ? $this->option('view-path') . '.' : '';
        $crudName = strtolower($this->option('crud-name') ?: $name);
        $crudNameSingular = str_singular($crudName);
        $modelName = $this->option('model-name') ?: $modelName;
        $routeGroup = $this->option('route-group') ? $this->option('route-group') . '/' : '';
        $requiredFields = $this->option('required-fields') ?: $requiredFields;
                
        $validationRules = '';
        if ($requiredFields) {
            $validationRules = "\$this->validate(\$request, " . $requiredFields . ");\n";
        }
        
        $data = compact([
            'viewPath',
            'crudName',
            'crudNameSingular',
            'modelName',
            'routeGroup',
            'validationRules',
        ]);
        
        $stub = $this->makeFromBladeString($stub, $data);

        return $this->replaceNamespace($stub, $controllerName)
                    ->replaceClass($stub, $controllerName);
    }
}
