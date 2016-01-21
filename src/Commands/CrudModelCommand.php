<?php

namespace Appzcoder\CrudGenerator\Commands;

class CrudModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:model
                            {name : The name of the model.}
                            {--table= : The name of the table.}
                            {--fillable= : The names of the fillable columns.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudgenerator.custom_template')
        ? config('crudgenerator.path') . '/model.stub'
        : __DIR__ . '/../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $table = $this->option('table') ?: $this->argument('name');
        $fillable = $this->option('fillable');

        $data = compact([
            'table',
            'fillable',
        ]);
        
        $stub = $this->makeFromBladeString($stub, $data);
        
        return $this->replaceNamespace($stub, $name)
                    ->replaceClass($stub, $name);
    }
}
