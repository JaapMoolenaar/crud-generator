<?php

namespace Appzcoder\CrudGenerator\Commands;

class CrudMigrationCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:migration
                            {name : The name of the migration.}
                            {--schema= : The name of the schema.}
                            {--pk=id : The name of the primary key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudgenerator.custom_template')
        ? config('crudgenerator.path') . '/migration.stub'
        : __DIR__ . '/../stubs/migration.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);
        $datePrefix = date('Y_m_d_His');

        return database_path('/migrations/') . $datePrefix . '_create_' . $name . '_table.php';
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

        $tableName = $this->argument('name');
        $className = 'Create' . ucwords($tableName) . 'Table';

        $schema = $this->option('schema');
        $fields = explode(',', $schema);

        $data = array();
        $x = 0;
        foreach ($fields as $field) {
            $fieldArray = explode(':', $field);
            $data[$x]['name'] = trim($fieldArray[0]);
            $data[$x]['type'] = trim($fieldArray[1]);
            $x++;
        }

        $schemaFieldsCollection = [];
        foreach ($data as $item) {
            switch ($item['type']) {
                case 'char':
                    $schemaFieldsCollection[] = "\$table->char('" . $item['name'] . "');";
                    break;

                case 'date':
                    $schemaFieldsCollection[] = "\$table->date('" . $item['name'] . "');";
                    break;

                case 'datetime':
                    $schemaFieldsCollection[] = "\$table->dateTime('" . $item['name'] . "');";
                    break;

                case 'time':
                    $schemaFieldsCollection[] = "\$table->time('" . $item['name'] . "');";
                    break;

                case 'timestamp':
                    $schemaFieldsCollection[] = "\$table->timestamp('" . $item['name'] . "');";
                    break;

                case 'text':
                    $schemaFieldsCollection[] = "\$table->text('" . $item['name'] . "');";
                    break;

                case 'mediumtext':
                    $schemaFieldsCollection[] = "\$table->mediumText('" . $item['name'] . "');";
                    break;

                case 'longtext':
                    $schemaFieldsCollection[] = "\$table->longText('" . $item['name'] . "');";
                    break;

                case 'json':
                    $schemaFieldsCollection[] = "\$table->json('" . $item['name'] . "');";
                    break;

                case 'jsonb':
                    $schemaFieldsCollection[] = "\$table->jsonb('" . $item['name'] . "');";
                    break;

                case 'binary':
                    $schemaFieldsCollection[] = "\$table->binary('" . $item['name'] . "');";
                    break;

                case 'number':
                case 'integer':
                    $schemaFieldsCollection[] = "\$table->integer('" . $item['name'] . "');";
                    break;

                case 'bigint':
                    $schemaFieldsCollection[] = "\$table->bigInteger('" . $item['name'] . "');";
                    break;

                case 'mediumint':
                    $schemaFieldsCollection[] = "\$table->mediumInteger('" . $item['name'] . "');";
                    break;

                case 'tinyint':
                    $schemaFieldsCollection[] = "\$table->tinyInteger('" . $item['name'] . "');";
                    break;

                case 'smallint':
                    $schemaFieldsCollection[] = "\$table->smallInteger('" . $item['name'] . "');";
                    break;

                case 'boolean':
                    $schemaFieldsCollection[] = "\$table->boolean('" . $item['name'] . "');";
                    break;

                case 'decimal':
                    $schemaFieldsCollection[] = "\$table->decimal('" . $item['name'] . "');";
                    break;

                case 'double':
                    $schemaFieldsCollection[] = "\$table->double('" . $item['name'] . "');";
                    break;

                case 'float':
                    $schemaFieldsCollection[] = "\$table->float('" . $item['name'] . "');";
                    break;

                default:
                    $schemaFieldsCollection[] = "\$table->string('" . $item['name'] . "');";
                    break;
            }
        }

        
        $schemaFields = implode(
            PHP_EOL.'            ', 
            $schemaFieldsCollection
        );
        
        $primaryKey = $this->option('pk');

        $schemaUp = "
        Schema::create('" . $tableName . "', function(Blueprint \$table) {
            \$table->increments('" . $primaryKey . "');
            " . $schemaFields . "
            \$table->timestamps();
        });";

        $schemaDown = "Schema::drop('" . $tableName . "');";

        $data = compact([
            'schemaUp',
            'schemaDown',
        ]);
        
        $stub = $this->makeFromBladeString($stub, $data);
        
        return $this->replaceClass($stub, $className);
    }
}
