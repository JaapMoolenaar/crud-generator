<?php

namespace Appzcoder\CrudGenerator\Commands;

use File;
use Illuminate\Console\Command;

class CrudViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:view
                            {name : The name of the Crud.}
                            {--fields= : The fields name for the form.}
                            {--view-path= : The name of the view path.}
                            {--route-group= : Prefix of the route group.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the Crud.';

    /**
     * View Directory Path.
     *
     * @var string
     */
    protected $stubsPath;

    /**
     *  Form field types collection.
     *
     * @var array
     */
    protected $typeLookup = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'time' => 'time',
        'boolean' => 'radio',
    ];
    
    /**
     *  Form field types not to be added to the overview table in the index templates.
     *
     * @var array
     */
    protected $typesNotShownInOverview = [
        'text',
        'mediumtext',
        'longtext',
        'json',
        'jsonb',
        'binary',
        'password',
    ];

    
    /**
     * Name of crud item (lc, plural)
     *
     * @var string
     */
    protected $crudName;
    
    /**
     * Name of crud item (ucfirst, plural)
     *
     * @var string
     */
    protected $crudNameCap;
    
    /**
     * Name of crud item (lc, singular)
     *
     * @var string
     */
    protected $crudNameSingular;
    
    /**
     * Name of the model (ucfirst, singular)
     *
     * @var string
     */
    protected $modelName;
    
    /**
     * A route group to set for this route
     *
     * @var string
     */
    protected $routeGroup;

    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->stubsPath = config('crudgenerator.custom_template')
        ? config('crudgenerator.path')
        : __DIR__ . '/../stubs/';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->crudName = $this->argument('name');
        $this->crudNameCap = ucwords($this->crudName);
        $this->crudNameSingular = str_singular($this->crudName);
        $this->modelName = ucwords($this->crudNameSingular);
        $this->routeGroup = ($this->option('route-group')) ? $this->option('route-group') . '/' : $this->option('route-group');

        $viewDirectory = rtrim(config('view.paths')[0], '\/').DIRECTORY_SEPARATOR;
        if ($this->option('view-path')) {
            $userPath = $this->option('view-path');
            $path = $viewDirectory . $userPath . DIRECTORY_SEPARATOR . $this->crudName . DIRECTORY_SEPARATOR;
        } else {
            $path = $viewDirectory . $this->crudName . DIRECTORY_SEPARATOR;
        }
        
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
        
        $this->info('Creating views in "'.$path.'"');
        
        
        $languagePath = rtrim(base_path('resources/lang/en'), '\/').DIRECTORY_SEPARATOR;

        $this->info('Creating languagefile in "'.$languagePath.'"');
        

        $fields = $this->option('fields');
        $fieldsArray = explode(',', $fields);

        $formFields = array();
        foreach ($fieldsArray as $item) {
            $itemArray = explode(':', $item);
            $field = [
                'name' => trim($itemArray[0]),
                'type' => trim($itemArray[1]),
                'required' => (isset($itemArray[2]) && (trim($itemArray[2]) == 'req' || trim($itemArray[2]) == 'required')) ? true : false
            ];

            if(!$this->doTypeLookup($field['type'])) {
                $this->error('Type "'.$field['type'].'" does not exist');

                $field['type'] = 'string';
            }
            
            $formFields[] = $field;
        }

        $formFieldsHtml = '';
        foreach ($formFields as $item) {
            $formFieldsHtml .= $this->createField($item);
        }

        // Form fields and label
        $formHeadingHtml = '';
        $formBodyHtml = '';
        $tableHtmlForShowView = '';
        $languageStrings = '';

        $i = 0;
        $indexIndentation = str_repeat("    ", 8);
        $showIndentation = str_repeat("    ", 6);
        foreach ($formFields as $key => $value) {

            $field = $value['name'];
            $fieldType = $value['type'];
            $label = ucwords(str_replace('_', ' ', $field));
            
            $languageStrings .= "    '" . $field . "' => '" . $label . "',\n";
            
            $tableHtmlForShowView .= $showIndentation.'<tr><th>'.$label.'</th><td>{{ $'.$this->crudNameSingular.'->' . $field . ' }}</td></tr>'."\n";
            
            if(in_array($fieldType, $this->typesNotShownInOverview)) {
                continue;
            }       
                        
            if ($i == 3) {
                continue;
            }
            
            $formHeadingHtml .= $indexIndentation.'<th>{{ trans(\'' . $this->crudName. '.' . $field . '\') }}</th>'."\n";

            if ($i == 0) {
                $formBodyHtml .= $indexIndentation.'<td><a href="{{ url(\''.$this->routeGroup.$this->crudName.'\', $item->id) }}">{{ $item->' . $field . ' }}</a></td>'."\n";
            } else {
                $formBodyHtml .= $indexIndentation.'<td>{{ $item->' . $field . ' }}</td>'."\n";
            }
            
            $i++;
        }
        
        // Trim the extra line endings
        $tableHtmlForShowView = rtrim($tableHtmlForShowView);
        $formHeadingHtml = rtrim($formHeadingHtml);
        $formBodyHtml = rtrim($formBodyHtml);

        $replaces = [
            '%%formHeadingHtml%%'       => $formHeadingHtml,
            '%%formBodyHtml%%'          => $formBodyHtml,
            '%%tableHtmlForShowView%%'  => $tableHtmlForShowView,
            '%%crudName%%'              => $this->crudName,
            '%%crudNameSingular%%'      => $this->crudNameSingular,
            '%%crudNameCap%%'           => $this->crudNameCap,
            '%%modelName%%'             => $this->modelName,
            '%%routeGroup%%'            => $this->routeGroup,
            '%%formFieldsHtml%%'        => $formFieldsHtml,
            '%%languageStrings%%'       => $languageStrings,
            '%%extendsLayout%%'         => config('crudgenerator.extend_layout', 'layouts.master'),
            '%%sectionName%%'           => config('crudgenerator.section_name', 'content'),
        ];
        
        // For language file
        $languageFile = $this->stubsPath . 'language.stub';
        $newLanguageFile = $languagePath . $this->crudName . '.php';
        if (!File::copy($languageFile, $newLanguageFile)) {
            $this->error("failed to copy $languageFile...\n");
        } else {
            File::put($newLanguageFile, str_replace(array_keys($replaces), $replaces, File::get($newLanguageFile)));
        }
        
        // For index.blade.php file
        $indexFile = $this->stubsPath . 'index.blade.stub';
        $newIndexFile = $path . 'index.blade.php';
        if (!File::copy($indexFile, $newIndexFile)) {
            $this->error("failed to copy $indexFile...\n");
        } else {
            File::put($newIndexFile, str_replace(array_keys($replaces), $replaces, File::get($newIndexFile)));
        }

        // For create.blade.php file
        $createFile = $this->stubsPath . 'create.blade.stub';
        $newCreateFile = $path . 'create.blade.php';
        if (!File::copy($createFile, $newCreateFile)) {
            $this->error("failed to copy $createFile...\n");
        } else {
            File::put($newCreateFile, str_replace(array_keys($replaces), $replaces, File::get($newCreateFile)));
        }

        // For edit.blade.php file
        $editFile = $this->stubsPath . 'edit.blade.stub';
        $newEditFile = $path . 'edit.blade.php';
        if (!File::copy($editFile, $newEditFile)) {
            $this->error("failed to copy $editFile...\n");
        } else {
            File::put($newEditFile, str_replace(array_keys($replaces), $replaces, File::get($newEditFile)));
        }

        // For edit.blade.php file
        $predeleteFile = $this->stubsPath . 'predelete.blade.stub';
        $newPredeleteFile = $path . 'predelete.blade.php';
        if (!File::copy($predeleteFile, $newPredeleteFile)) {
            $this->error("failed to copy $predeleteFile...\n");
        } else {
            File::put($newPredeleteFile, str_replace(array_keys($replaces), $replaces, File::get($newPredeleteFile)));
        }

        // For show.blade.php file
        $showFile = $this->stubsPath . 'show.blade.stub';
        $newShowFile = $path . 'show.blade.php';
        if (!File::copy($showFile, $newShowFile)) {
            $this->error("failed to copy $showFile...\n");
        } else {
            File::put($newShowFile, str_replace(array_keys($replaces), $replaces, File::get($newShowFile)));
        }

        // Should we include a master template?
        if(config('crudgenerator.extend_layout', 'layouts.master') == 'layouts.master') {
            // For layouts/master.blade.php file
            $layoutsDirPath = base_path('resources/views/layouts/');
            if (!File::isDirectory($layoutsDirPath)) {
                File::makeDirectory($layoutsDirPath);
            }

            $layoutsFile = $this->stubsPath . 'master.blade.stub';
            $newLayoutsFile = $layoutsDirPath . 'master.blade.php';

            if (!File::exists($newLayoutsFile)) {
                if (!File::copy($layoutsFile, $newLayoutsFile)) {
                    $this->error("failed to copy $layoutsFile...\n");
                }
            }
        }
        
        $this->info('View created successfully.');
    }

    /**
     * Form field wrapper.
     *
     * @param  string  $item
     * @param  string  $field
     *
     * @return void
     */
    protected function wrapField($item, $field)
    {
        // the indentation consists of tabs expanded to 4 spaces
        $indentation = str_repeat("    ", config('crudgenerator.formgroupindent', 5));
        
        $formGroup =
<<<EOD
$indentation<div class="form-group {{ \$errors->has('%1\$s') ? 'has-error' : ''}}">
$indentation    {!! Form::label('%1\$s', trans('{$this->crudName}.%1\$s').': ', ['class' => 'col-sm-3 control-label']) !!}
$indentation    <div class="col-sm-6">
$indentation        %3\$s
$indentation        {!! \$errors->first('%1\$s', '<p class="help-block">:message</p>') !!}
$indentation    </div>
$indentation</div>\n
EOD;

        return sprintf($formGroup, $item['name'], ucwords(strtolower(str_replace('_', ' ', $item['name']))), $field);
    }

    /**
     * Form field generator.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function doTypeLookup($type)
    {
        if(array_key_exists($type, $this->typeLookup)) {
            return $this->typeLookup[$type];
        }
        
        return false;
    }
    
    /**
     * Form field generator.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function createField($item)
    {        
        switch ($this->doTypeLookup($item['type'])) {
            case 'password':
                return $this->createPasswordField($item);
                break;
            case 'datetime-local':
            case 'time':
                return $this->createInputField($item);
                break;
            case 'radio':
                return $this->createRadioField($item);
                break;
            default: // text
                return $this->createFormField($item);
        }
    }

    /**
     * Create a specific field using the form helper.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function createFormField($item)
    {
        
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";
        
        return $this->wrapField(
            $item,
            "{!! Form::" . $this->typeLookup[$item['type']] . "('" . $item['name'] . "', null, ['class' => 'form-control'$required]) !!}"
        );
    }

    /**
     * Create a password field using the form helper.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function createPasswordField($item)
    {
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";

        return $this->wrapField(
            $item,
            "{!! Form::password('" . $item['name'] . "', ['class' => 'form-control'$required]) !!}"
        );
    }

    /**
     * Create a generic input field using the form helper.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function createInputField($item)
    {
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";

        return $this->wrapField(
            $item,
            "{!! Form::input('" . $this->typeLookup[$item['type']] . "', '" . $item['name'] . "', null, ['class' => 'form-control'$required]) !!}"
        );
    }

    /**
     * Create a yes/no radio button group using the form helper.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function createRadioField($item)
    {
        // the indentation consists of tabs expanded to 4 spaces
        $indentation = str_repeat("    ", 1);
        
        $field =
<<<EOD
$indentation<div class="checkbox">
$indentation    <label>{!! Form::radio('%1\$s', '1') !!} <span>Yes</span></label>
$indentation</div>
$indentation<div class="checkbox">
$indentation    <label>{!! Form::radio('%1\$s', '0', true) !!} <span>No</span></label>
$indentation</div>
EOD;

        return $this->wrapField($item, sprintf($field, $item['name']));
    }

}
