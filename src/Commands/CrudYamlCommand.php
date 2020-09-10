<?php

namespace Okami101\LaravelAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class CrudYamlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'crud:yaml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all necessary server-side resources from YML file descriptor';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->argument('file');

        if ($this->files->isFile($input)) {
            $this->loadFileDescriptor($input);

            return;
        }

        if ($this->files->isDirectory($input)) {
            /**
             * Parse all yaml files
             */
            foreach ($this->files->files($input) as $file) {
                $this->loadFileDescriptor($file);
            }

            return;
        }

        $this->error('Invalid input !');
    }

    private function loadFileDescriptor($file)
    {
        $descriptor = Yaml::parseFile($file);

        foreach ($descriptor['resources'] as $key => $resource) {
            $model = Str::studly(Str::singular($key));

            /**
             * If set, only import specified name
             */
            if (($name = $this->option('name')) && $name !== $model) {
                continue;
            }

            $this->call('crud:make', [
                'name' => $model,
                '--translatable' => $resource['translatable'] ?? [],
                '--searchable' => $resource['searchable'] ?? [],
                '--sortable' => $resource['sortable'] ?? [],
                '--include' => $resource['include'] ?? [],
                '--filterable' => $this->getFiltrableFields($resource)->toArray(),
                '--media' => $this->getMediaFields($resource)->toArray(),
                '--schema' => $this->getFieldSchemas($resource)->toArray(),
                '--migration' => $this->option('migration'),
                '--factory' => $this->option('factory'),
                '--seed' => $this->option('seed'),
                '--force' => $this->option('force'),
            ]);
        }
    }

    private function getFieldSchemas($resource)
    {
        return collect($resource['fields'] ?? [])->filter(function ($field, $name) {
            return (! isset($field['db']) || $field['db'] !== false) && ! in_array($name, ['created_at', 'updated_at'], true);
        })
            ->map(function ($field, $name) use ($resource) {
                $name = $field['db']['name'] ?? $name;
                $type = $field['db']['type'] ?? 'string';

                /**
                 * JSON required if translatable
                 */
                if (in_array($name, $resource['translatable'] ?? [], true)) {
                    $type = 'json';
                }

                $schema = "$name:$type";

                /**
                 * Specific database attribute
                 */
                if (! empty($field['db']['options'])) {
                    foreach ($field['db']['options'] as $attribute) {
                        $schema .= ":$attribute";
                    }
                }

                return $schema;
            })->values();
    }

    private function getFiltrableFields($resource)
    {
        return collect($resource['filterable'] ?? [])->map(function ($name) use ($resource) {
            $internal = $resource['fields'][$name]['db']['name'] ?? null;

            if ($internal) {
                return "$name:$internal";
            }

            return $name;
        })->values();
    }

    private function getMediaFields($resource)
    {
        return collect($resource['media'] ?? [])->map(function ($name) use ($resource) {
            $multiple = $resource['fields'][$name]['form']['multiple'] ?? false;

            return "$name:$multiple";
        })->values();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['file', InputArgument::REQUIRED, 'The YAML file descriptor or the directory which contains YAML descriptors'],
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
            ['name', null, InputOption::VALUE_OPTIONAL, 'Name of model to import, if not set, all will be imported'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Generate migration schema with pre-generated fields'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder file for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
