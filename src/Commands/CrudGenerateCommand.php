<?php

namespace Vtec\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class CrudGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'crud:generate';

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

        foreach ($descriptor as $name => $resource) {
            $this->call('crud:make', [
                'name' => $resource['model'],
                '--fields' => $this->getFields($resource),
                '--translatable' => $this->getFieldNames($resource, 'translatable'),
                '--searchable' => $this->getFieldNames($resource, 'searchable'),
                '--sortable' => $this->getFieldNames($resource, 'sortable'),
                '--mediable' => $this->getMediableFields($resource),
                '--schema' => $this->getFieldSchemas($resource)->implode(', '),
                '--factory' => $this->option('factory'),
                '--seed' => $this->option('seed'),
                '--force' => $this->option('force'),
            ]);
        }
    }

    private function getDatabaseFields($resource)
    {
        return collect($resource['fields'])->filter(function ($field) {
            $type = $field['type'] ?? 'string';

            return ! in_array($type, ['file', 'image']);
        });
    }

    private function getFields($resource)
    {
        return $this->getDatabaseFields($resource)->map(function ($field, $name) {
            $type = $field['type'] ?? 'string';

            return "$name:$type";
        })->values();
    }

    private function getFieldSchemas($resource)
    {
        return $this->getDatabaseFields($resource)->map(function ($field, $name) {
            $type = $field['db']['type'] ?? 'string';

            /**
             * JSON required if translatable
             */
            if (! empty($field['translatable'])) {
                $type = 'json';
            }

            $schema = "$name:$type";

            if (empty($field['required'])) {
                $schema .= ':nullable';
            }

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

    private function getFieldNames($resource, $filter = null)
    {
        return collect($resource['fields'])->filter(function ($field) use ($filter) {
            if ($filter) {
                return $field[$filter] ?? false;
            }

            return true;
        })->keys();
    }

    private function getMediableFields($resource)
    {
        return collect($resource['fields'])->filter(function ($field) {
            $type = $field['type'] ?? 'string';

            return in_array($type, ['file', 'image']);
        })->map(function ($field, $name) {
            $multiple = $field['multiple'] ?? false;

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
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder file for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
