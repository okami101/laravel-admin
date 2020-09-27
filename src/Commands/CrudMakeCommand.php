<?php

namespace Okami101\LaravelAdmin\Commands;

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
            'namespace' => '\Models',
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
        if ($this->option('migration')) {
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
            if (! $this->hasMedia() && $this->isTranslatable()) {
                $stub .= '.translatable';
            }
            if ($this->hasMedia() && ! $this->isTranslatable()) {
                $stub .= '.media';
            }
            if (! $this->hasMedia() && ! $this->isTranslatable()) {
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

    private function hasMedia()
    {
        return ! empty($this->option('media'));
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
        $namespaceModel = $this->qualifyModel($this->argument('name'));
        $model = class_basename($namespaceModel);
        $class = parent::replaceClass($stub, $name);

        return str_replace([
            '{{ fillable }}',
            '{{ casts }}',
            '{{ translatable }}',
            '{{ searchable }}',
            '{{ filters }}',
            '{{ sortable }}',
            '{{ include }}',
            '{{ media }}',
            '{{ namespacedModel }}',
            '{{ model }}',
            '{{ modelVariable }}',
            '{{ user }}',
        ], [
            $this->getArrayString($this->getFields()->keys()),
            $this->getArrayWithKeysString($this->getCasts()),
            $this->getArrayString($this->getTranslatableFields()),
            $this->getArrayString($this->getSearchableFields()),
            $this->getFilterableFields()->implode("\n                    "),
            $this->getArrayString($this->getSortableFields()),
            $this->getArrayString($this->getIncludeFields()),
            $this->getMediaCodeLines($this->getMediaFields()),
            $namespaceModel,
            $model,
            Str::camel($model),
            class_basename($this->userProviderModel()),
        ], $class);
    }

    private function getFormattedInputArray($input)
    {
        $value = $this->option($input);

        if (empty($value)) {
            return collect();
        }

        if (is_array($value)) {
            return collect($value);
        }

        return collect(array_map('trim', explode(',', $value)));
    }

    private function getFields()
    {
        return $this->getFormattedInputArray('schema')->mapWithKeys(function ($field) {
            $segments = explode(':', $field);

            return [$segments[0] => $segments[1]];
        });
    }

    private function getTranslatableFields()
    {
        return $this->getFormattedInputArray('translatable');
    }

    private function getSearchableFields()
    {
        return $this->getFormattedInputArray('searchable');
    }

    private function getSortableFields()
    {
        return $this->getFormattedInputArray('sortable');
    }

    private function getIncludeFields()
    {
        return $this->getFormattedInputArray('include');
    }

    private function getFilterableFields()
    {
        return $this->getFormattedInputArray('filterable')->map(function ($field) {
            $name = $field;
            $segments = explode(':', $field);

            if (count($segments) === 2) {
                [$name, $internal] = $segments;
            }

            $filter = 'exact';

            $type = $this->getFields()->get($name);

            if (in_array($type, ['string', 'json'], true)) {
                // Prefer partial as default SQL filter for text
                $filter = 'partial';
            }

            if (! empty($internal)) {
                return "AllowedFilter::$filter('$name', '$internal'),";
            }

            return "AllowedFilter::$filter('$name'),";
        });
    }

    private function getMediaFields()
    {
        return $this->getFormattedInputArray('media')->mapWithKeys(function ($field) {
            $segments = explode(':', $field);

            return [$segments[0] => $segments[1]];
        });
    }

    private function getCasts()
    {
        return collect($this->getFields())->filter(function ($type, $name) {
            return ! Str::endsWith($name, '_id')
                && ! in_array($name, $this->getTranslatableFields()->toArray(), true)
                && in_array($type, [
                    'integer',
                    'float',
                    'double',
                    'decimal',
                    'boolean',
                    'json',
                    'date',
                    'datetime',
                    'timestamp',
                ]);
        })->map(function ($type) {
            if ($type === 'decimal') {
                return "$type:2";
            }
            if ($type === 'json') {
                return "array";
            }

            return $type;
        });
    }

    private function getMediaCodeLines($array)
    {
        return collect($array)->map(function ($multiple, $collection) {
            $line = "\$this->addMediaCollection('$collection')";
            if (! filter_var($multiple, FILTER_VALIDATE_BOOL)) {
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
        $value = $this->option('schema');

        $this->call('make:migration:schema', [
            'name' => "create_{$table}_table",
            '--model' => false,
            '--schema' => is_array($value) ? implode(',', $value) : $value,
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
        $model = class_basename($this->argument('name'));
        $slug = Str::snake(Str::pluralStudly($model));
        $code = "'$slug' => 'App\Http\Controllers\{$model}Controller'";

        $routeFile = base_path('routes/api.php');
        if (Str::contains($this->files->get($routeFile), $code)) {
            return;
        }

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
        $code,
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
            ['schema', null, InputOption::VALUE_OPTIONAL, 'List of database fields for migration'],
            ['media', null, InputOption::VALUE_OPTIONAL, 'List of media fields (field:multiple)'],
            ['translatable', null, InputOption::VALUE_OPTIONAL, 'List of translatable fields'],
            ['searchable', null, InputOption::VALUE_OPTIONAL, 'List of searchable fields'],
            ['sortable', null, InputOption::VALUE_OPTIONAL, 'List of sortable fields'],
            ['include', null, InputOption::VALUE_OPTIONAL, 'List of included related resources'],
            ['filterable', null, InputOption::VALUE_OPTIONAL, 'List of custom filterable fields (field:internal)'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory file for the model'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder file for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
