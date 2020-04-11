<?php

namespace Vtec\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class CrudInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'crud:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick start install';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        /*$dependencies = ['laravel/ui'];
        $devDependencies = ['laracasts/generators'];

        if ($installLaravelSanctum = $this->confirm('Install laravel/sanctum to provide SPA authentication (mandatory for sanctum provider) ?', true)) {
            $dependencies[] = 'laravel/sanctum';
        }
        if ($installLaravelElfinder = $this->confirm('Install barryvdh/laravel-elfinder to provide an admin interface for File Management ?')) {
            $dependencies[] = 'barryvdh/laravel-elfinder';
        }
        if ($installLaravelClockwork = $this->confirm('Install itsgoingd/clockwork to provide debugging and profiling ?', true)) {
            $dependencies[] = 'itsgoingd/clockwork';
        }
        if ($installLaravelIdeHelper = $this->confirm('Install barryvdh/laravel-ide-helper to provide full autocompletion ?', true)) {
            $devDependencies[] = 'barryvdh/laravel-ide-helper';
        }
        if ($installPhpCsFixer = $this->confirm('Install friendsofphp/php-cs-fixer to provide code styling ?', true)) {
            $devDependencies[] = 'friendsofphp/php-cs-fixer';
        }

        $this->installDependencies($dependencies);
        $this->installDependencies($devDependencies, true);
        $this->updateDependencies();*/

        $installLaravelSanctum = true;
        $installLaravelElfinder = true;
        $installLaravelClockwork = true;
        $installLaravelIdeHelper = true;
        $installPhpCsFixer = true;

        $this->configureLaravelMediaLibrary();
        $this->changeAuthenticationRedirect();
        $this->addAuthentificationControllers();
        $this->addAccountController();

        if ($installLaravelSanctum) {
            $this->configureLaravelSanctum();
        }

        if ($installLaravelElfinder) {
            $this->configureLaravelElfinder();
        }

        if ($installLaravelClockwork) {
            $this->configureLaravelClockwork();
        }

        if ($installLaravelIdeHelper) {
            $this->configureLaravelIdeHelper();
        }

        if ($installPhpCsFixer) {
            $this->configurePhpCsFixer();
        }

        //if ($this->confirm('Remove all assets files (no need if you use frontend framework as Nuxt.js) ?')) {
            $this->removeAssets();
        //}

        //if ($this->confirm('Install Docker files ?', true)) {
            $this->addDockerfiles();
        //}
    }

    /**
     * Replace default laravel redirect to Vtec admin url
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function changeAuthenticationRedirect()
    {
        $this->line('Change default authentication redirect');

        $file = app_path('Http/Middleware/Authenticate.php');
        $content = $this->files->get($file);

        $this->files->replace($file, Str::replaceArray("route('login')", ["config('admin.url')"], $content));
    }

    private function addAuthentificationControllers()
    {
        $this->line('Add authentification controllers');
        $this->call('ui:controllers');
    }

    private function addAccountController()
    {
        $this->line('Add account controller');
        $this->files->copy(__DIR__ . '/../../files/.php_cs.dist', app_path('Http/Controllers'));
        $this->files->copy(__DIR__ . '/../../files/.php_cs.dist', app_path('.php_cs.dist'));
    }

    private function configureLaravelMediaLibrary()
    {
        $this->line('Configure Laravel Media Library');
        $this->call('vendor:publish', [
            '--provider' => MediaLibraryServiceProvider::class,
            '--tag' => ['config', 'migrations']
        ]);
    }

    private function configureLaravelSanctum()
    {
        $this->line('Configure Laravel Sanctum');
        $this->call('vendor:publish', [
            '--provider' => 'Laravel\Sanctum\SanctumServiceProvider',
            '--tag' => 'sanctum-config'
        ]);
    }

    private function configureLaravelElfinder()
    {
        $this->line('Configure Laravel Elfinder');
        $this->call('vendor:publish', [
            '--provider' => 'Barryvdh\Elfinder\ElfinderServiceProvider',
        ]);

        /**
         * Keep only tinymce5 bridge which is the only used by Vtec Admin
         */
        foreach($this->files->allFiles(resource_path('views\vendor\elfinder')) as $file) {
            if ($file->getFilename() !== 'tinymce5.blade.php') {
                unlink($file);
            }
        }
    }

    private function configureLaravelClockwork()
    {
        $this->line('Configure Laravel Clockwork');
        $this->call('vendor:publish', [
            '--provider' => 'Clockwork\Support\Laravel\ClockworkServiceProvider',
        ]);
    }

    private function configureLaravelIdeHelper()
    {
        $this->line('Configure Laravel IDE Helper');
        $this->call('vendor:publish', [
            '--provider' => 'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
            '--tag' => 'config'
        ]);
    }

    private function configurePhpCsFixer()
    {
        $this->line('Configure PHP CS Fixer');
        $this->files->copy(__DIR__ . '/../../files/.php_cs.dist', base_path('.php_cs.dist'));
    }

    private function removeAssets()
    {
        $this->line('Remove assets');
        $this->files->delete(base_path('package.json'), base_path('package-lock.json'), base_path('webpack.mix.js'));
        $this->files->deleteDirectory(resource_path('js'));
        $this->files->deleteDirectory(resource_path('sass'));
    }

    private function addDockerfiles()
    {
        $this->line('Add docker files');
        $this->files->copyDirectory(__DIR__ . '/../../files/docker', base_path('docker'));
        $this->files->copy(__DIR__ . '/../../files/docker-compose.yml', base_path('docker-compose.yml'));
        $this->files->copy(__DIR__ . '/../../files/Dockerfile', base_path('Dockerfile'));
    }

    private function installDependencies(array $dependencies, bool $dev = false)
    {
        $this->line($dev ? 'Add dev dependencies' : 'Add dependencies');

        $command = ['composer', 'require', ...$dependencies, '--no-update'];

        if ($dev) {
            $command[] = '--dev';
        }

        $process = new Process($command);
        $process->run();
    }

    private function updateDependencies()
    {
        $this->line('Installing dependencies');

        $command = ['composer', 'update'];

        $process = new Process($command, null, null, null, null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->comment($buffer);
            } else {
                $this->line($buffer);
            }
        });
    }
}
