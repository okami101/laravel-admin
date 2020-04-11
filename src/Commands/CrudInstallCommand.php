<?php

namespace Vtec\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

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
        $dependencies = ['laravel/ui'];
        $devDependencies = ['laracasts/generators'];

        if ($installLaravelSanctum = $this->confirm('Install laravel/sanctum to provide SPA authentication (mandatory for sanctum provider) ?', true)) {
            $dependencies[] = 'laravel/sanctum';
        }
        if ($installLaravelElfinder = $this->confirm('Install barryvdh/laravel-elfinder to provide an admin interface for File Management ?', true)) {
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
        $this->updateDependencies();

        /*$this->changeAuthenticateRedirect();

        $this->addAuthentificationControllers();
        $this->addAccountController();

        if ($installLaravelSanctum) {
            $this->installLaravelSanctum();
        }

        if ($installLaravelElfinder) {
            $this->installLaravelElfinder();
        }

        if ($installLaravelClockwork) {
            $this->installLaravelClockwork();
        }

        if ($installLaravelIdeHelper) {
            $this->installLaravelIdeHelper();
        }

        if ($installPhpCsFixer) {
            $this->installPhpCsFixer();
        }

        if ($this->confirm('Remove package.json and Laravel Mix (no need if you use frontend framework as Nuxt.js) ?')) {
            $this->removeLaravelMix();
        }

        if ($this->confirm('Install Docker files ?', true)) {
            $this->installDocker();
        }*/
    }

    /**
     * Replace default laravel redirect to Vtec admin url
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function changeAuthenticateRedirect()
    {
        $this->line('Replace default redirect');

        $file = app_path('Http/Middleware/Authenticate.php');
        $content = $this->files->get($file);

        $this->files->replace($file, Str::replaceArray("route('login')", ["config('admin.url')"], $content));
    }

    private function addAuthentificationControllers()
    {

    }

    private function addAccountController()
    {

    }

    private function installLaravelSanctum()
    {

    }

    private function installLaravelElfinder()
    {
        $this->line('Installing barryvdh/laravel-elfinder');
    }

    private function installLaravelClockwork()
    {

    }

    private function installLaravelIdeHelper()
    {

    }

    private function installPhpCsFixer()
    {

    }

    private function removeLaravelMix()
    {

    }

    private function installDocker()
    {

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
