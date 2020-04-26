<?php

namespace Vtec\Crud\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'crud:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all necessary server-side resource stubs';

    /**
     * Stubs to generate
     *
     * @var array
     */
    protected $stubs = [
        'Model' => [
            'stub' => 'model',
        ],
        'Controller' => [
            'stub' => 'controller',
            'namespace' => '\Http\Controllers',
            'suffix' => 'Controller',
        ],
        'Policy' => [
            'stub' => 'policy',
            'namespace' => '\Policies',
            'suffix' => 'Policy',
        ],
        'StoreRequest' => [
            'stub' => 'request',
            'namespace' => '\Http\Requests',
            'prefix' => 'Store',
        ],
        'UpdateRequest' => [
            'stub' => 'request',
            'namespace' => '\Http\Requests',
            'prefix' => 'Update',
        ],
        'Resource' => [
            'stub' => 'resource',
            'namespace' => '\Http\Resources',
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /**
         * Generate resource files
         */
        collect($this->stubs)->each(function ($name, $type) {
            $this->type = $type;

            parent::handle();
        });

        /**
         * Generate resource data (migration, factory and seeder)
         */
        if ($this->option('schema')) {
            $this->createMigration();
        }

        if ($this->option('factory')) {
            $this->createFactory();
        }

        if ($this->option('seed')) {
            $this->createSeeder();
        }

        $this->createRoutes();
    }

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        $stub = $this->getType()['stub'];

        if ($stub === 'model') {
            /**
             * Take specific stubs
             */
            if (! $this->isMediable() && $this->isTranslatable()) {
                $stub .= '.translatable';
            }
            if ($this->isMediable() && ! $this->isTranslatable()) {
                $stub .= '.mediable';
            }
            if (! $this->isMediable() && ! $this->isTranslatable()) {
                $stub .= '.plain';
            }
        }

        return __DIR__."/../../stubs/{$stub}.stub";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.($this->getType()['namespace'] ?? null);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));
        $info = $this->getType();

        if ($info['prefix'] ?? null) {
            $name = "{$info['prefix']}$name";
        }

        if ($info['suffix'] ?? null) {
            $name = "$name{$info['suffix']}";
        }

        return $name;
    }

    private function getType()
    {
        return $this->stubs[$this->type];
    }

    private function isMediable()
    {
        return ! empty($this->option('mediable'));
    }

    private function isTranslatable()
    {
        return ! empty($this->option('translatable'));
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $namespacedModel = $this->rootNamespace().$this->argument('name');
        $model = class_basename($namespacedModel);
        $class = parent::replaceClass($stub, $name);

        return str_replace([
            '{{ fields }}',
            '{{ casts }}',
            '{{ translatable }}',
            '{{ searchable }}',
            '{{ filters }}',
            '{{ sortable }}',
            '{{ mediable }}',
            '{{ namespacedModel }}',
            '{{ model }}',
            '{{ modelVariable }}',
            '{{ user }}',
        ], [
            $this->getArrayString(['id', ...$this->getFields()->keys()]),
            $this->getArrayWithKeysString($this->getCasts()),
            $this->getArrayString($this->getTranslatableFields()),
            $this->getArrayString($this->getSearchableFields()),
            $this->getFilterableFields()->implode("\n                    "),
            $this->getArrayString($this->getSortableFields()),
            $this->getMediaCodeLines($this->getMediableFields()),
            $namespacedModel,
            $model,
            Str::camel($model),
            class_basename($this->userProviderModel()),
        ], $class);
    }

    private function getFields()
    {
        return collect($this->option('fields'))->mapWithKeys(function ($field) {
            $segments = explode(':', $field);

            return [$segments[0] => $segments[1]];
        });
    }

    private function getTranslatableFields()
    {
        return collect($this->option('translatable'));
    }

    private function getSearchableFields()
    {
        return collect($this->option('searchable'));
    }

    private function getSortableFields()
    {
        return collect($this->option('sortable'));
    }

    private function getFilterableFields()
    {
        return collect($this->option('filterable'))->map(function ($field) {
            return "AllowedFilter::partial('$field'),";
        });
    }

    private function getMediableFields()
    {
        return collect($this->option('mediable'))->mapWithKeys(function ($field) {
            $segments = explode(':', $field);

            return [$segments[0] => $segments[1]];
        });
    }

    private function getCasts()
    {
        return collect($this->getFields())->filter(function ($type) {
            return in_array($type, [
                'integer',
                'real',
                'float',
                'double',
                'decimal',
                'boolean',
                'object',
                'array',
                'collection',
                'date',
                'datetime',
                'timestamp',
            ]);
        })->map(function ($type) {
            if ($type === 'decimal') {
                return "$type:2";
            }

            return $type;
        });
    }

    private function getMediaCodeLines($array)
    {
        return collect($array)->map(function ($multiple, $collection) {
            $line = "\$this->addMediaCollection('$collection')";
            if (! $multiple) {
                $line .= '->singleFile()';
            }

            return "$line;";
        })->implode("\n        ");
    }

    private function getArrayString($array)
    {
        return collect($array)->map(function ($item) {
            return "'$item'";
        })->values()->implode(', ');
    }

    private function getArrayWithKeysString($array)
    {
        return collect($array)->map(function ($item, $key) {
            return "'$key' => '$item'";
        })->values()->implode(', ');
    }

    /**
     * Create a model factory for the model.
     *
     * @return void
     */
    protected function createFactory()
    {
        $factory = Str::studly(class_basename($this->argument('name')));

        $this->call('make:factory', [
            'name' => "{$factory}Factory",
            '--model' => $this->argument('name'),
        ]);
    }

    /**
     * Create a migration file for the model with schema support.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        $this->call('make:migration:schema', [
            'name' => "create_{$table}_table",
            '--model' => false,
            '--schema' => collect($this->option('schema'))->implode(', '),
        ]);
    }

    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $seeder = Str::studly(class_basename($this->argument('name')));

        $this->call('make:seeder', [
            'name' => "{$seeder}Seeder",
        ]);
    }

    /**
     * Add routes to api
     */
    protected function createRoutes()
    {
        $model = $this->argument('name');
        $slug = Str::slug(Str::plural($model));

        $routeFile = base_path('routes/api.php');
        $content = file($routeFile, FILE_IGNORE_NEW_LINES);

        $lines = count($content);
        $startLine = array_search('Route::apiResources', $content, true);

        for ($i = $startLine + 1; $i < $lines; $i++) {
            if (Str::contains($content[$i], ']);')) {
                $endLine = $i;

                break;
            }
        }

        array_splice(
            $content,
            $endLine,
            0,
            <<<EOF
        '$slug' => '{$model}Controller',
EOF
        );
        $this->files->put($routeFile, implode(PHP_EOL, $content) . PHP_EOL);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['schema', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of database fields for migration'],
            ['fields', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of fields (field:type)'],
            ['mediable', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of mediable fields (field:multiple)'],
            ['translatable', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of translatable fields'],
            ['searchable', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of searchable fields'],
            ['sortable', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of sortable fields'],
            ['filterable', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of custom filterable fields'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder file for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
