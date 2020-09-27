<?php

namespace Okami101\LaravelAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Okami101\LaravelAdmin\AdminServiceProvider;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'admin:install';

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
        /**
         * Composer dependencies
         */
        $dependencies = ['laravel/fortify', 'spatie/laravel-query-builder'];
        $devDependencies = ['laracasts/generators'];

        if ($this->confirm('Install spatie/laravel-translatable to provide database models translation ?', true)) {
            $dependencies[] = 'spatie/laravel-translatable';
        }
        if ($installLaravelMediaLibrary = $this->confirm('Install spatie/laravel-medialibrary to provide database file management ?', true)) {
            $dependencies[] = 'spatie/laravel-medialibrary';
        }
        if ($installLaravelSanctum = $this->confirm('Install laravel/sanctum to provide SPA authentication (required if you choose sanctum provider) ?', true)) {
            $dependencies[] = 'laravel/sanctum';
        }
        if ($installLaravelElfinder = $this->confirm('Install barryvdh/laravel-elfinder to provide an admin interface for File Management ?', true)) {
            $dependencies[] = 'barryvdh/laravel-elfinder';
        }
        if ($this->confirm('Install itsgoingd/clockwork to provide debugging and profiling ?', true)) {
            $dependencies[] = 'itsgoingd/clockwork';
        }
        if ($this->confirm('Install beyondcode/laravel-dump-server to provide dump server for API ?', true)) {
            $devDependencies[] = 'beyondcode/laravel-dump-server';
        }
        if ($installLaravelIdeHelper = $this->confirm('Install barryvdh/laravel-ide-helper to provide full autocompletion ?', true)) {
            $devDependencies[] = 'barryvdh/laravel-ide-helper';
        }
        if ($installPhpCsFixer = $this->confirm('Install friendsofphp/php-cs-fixer to provide code styling ?', true)) {
            $devDependencies[] = 'friendsofphp/php-cs-fixer';
        }

        $this->installDependencies($dependencies);
        $this->installDependencies($devDependencies, true);

        $this->line('Installing dependencies');
        $this->executeCommand(['composer', 'update']);

        /**
         * Enable Laravel Fortify features
         */
        $this->configureFortify();

        /**
         * Specific per-package preconfiguration
         */
        if ($installLaravelSanctum) {
            $this->configureLaravelSanctum();
        }

        if ($installLaravelMediaLibrary) {
            $this->configureMediaLibrary();
        }

        if ($installLaravelElfinder) {
            $this->configureLaravelElfinder();
        }

        if ($installLaravelIdeHelper) {
            $this->configureLaravelIdeHelper();
        }

        if ($installPhpCsFixer) {
            $this->configurePhpCsFixer();
        }

        if ($this->confirm('Remove all assets files (no need if you use frontend framework as Nuxt.js) ?', true)) {
            $this->removeAssets();
        }

        if ($this->confirm('Install Docker files ?', true)) {
            $this->addDockerfiles();
        }

        /**
         * Auto included package publish
         */
        $this->call('vendor:publish', [
            '--provider' => AdminServiceProvider::class,
            '--tag' => 'config',
            '--force' => true,
        ]);

        /**
         * Default Laravel configuration with auth and user routes and api controllers
         */
        $this->changeAuthenticationRedirect();
        $this->addUserController();
        $this->configureRoutes();

        /**
         * Vue CLI Plugin install from preset
         */
        $this->call(UICommand::class);
    }

    private function configureFortify()
    {
        $this->line('Configure Laravel Fortify');

        $this->executeCommand(['php', 'artisan', 'vendor:publish', '--provider', 'Laravel\Fortify\FortifyServiceProvider']);

        /**
         * Register service provider inside app
         */
        $this->insertCode(
            config_path('app.php'),
            'FortifyServiceProvider',
            'RouteServiceProvider',
            <<<EOF
        App\Providers\FortifyServiceProvider::class,

EOF
        );
    }

    private function configureLaravelSanctum()
    {
        $this->line('Configure Laravel Sanctum');

        /**
         * Insert middlewares inside HTTP Kernel
         */
        $this->insertCode(
            app_path('Http/Kernel.php'),
            'EnsureFrontendRequestsAreStateful',
            'api',
            <<<EOF
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Okami101\LaravelAdmin\Http\Middleware\Impersonate::class,

EOF
        );
    }

    private function insertCode($file, $checker, $search, $code)
    {
        if (Str::contains($this->files->get($file), $checker)) {
            return;
        }

        $lines = file($file);

        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $search) !== false) {
                array_splice(
                    $lines,
                    $lineNumber + 1,
                    0,
                    $code
                );

                break;
            }
        }
        $this->files->replace($file, $lines);
    }

    private function configureMediaLibrary()
    {
        $this->line('Configure Spatie Media Library');

        $this->executeCommand([
            'php',
            'artisan',
            'vendor:publish',
            '--provider',
            'Spatie\MediaLibrary\MediaLibraryServiceProvider',
            '--tag',
            'config',
            '--tag',
            'migrations',
        ]);
    }

    /**
     * Replace default laravel redirect to admin url
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

    private function addUserController()
    {
        $this->line('Add user and account controller');

        /**
         * Add user controller
         */
        if (! $this->files->isDirectory(app_path('Http/Requests'))) {
            $this->files->makeDirectory(app_path('Http/Requests'));
        }
        if (! $this->files->isDirectory(app_path('Http/Resources'))) {
            $this->files->makeDirectory(app_path('Http/Resources'));
        }
        $this->files->copy(__DIR__ . '/../../stubs/user/model.stub', app_path('User.php'));
        $this->files->copy(__DIR__ . '/../../stubs/user/resource.stub', app_path('Http/Resources/User.php'));
        $this->files->copy(__DIR__ . '/../../stubs/user/request.store.stub', app_path('Http/Requests/StoreUser.php'));
        $this->files->copy(__DIR__ . '/../../stubs/user/request.update.stub', app_path('Http/Requests/UpdateUser.php'));
        $this->files->copy(__DIR__ . '/../../stubs/user/controller.stub', app_path('Http/Controllers/UserController.php'));
        $this->files->copy(__DIR__ . '/../../stubs/user/account.controller.stub', app_path('Http/Controllers/AccountController.php'));
    }

    private function configureRoutes()
    {
        /**
         * Add web and api routes
         */
        $this->files->copy(__DIR__ . '/../../stubs/routes/api.stub', base_path('routes/api.php'));
    }

    private function configureLaravelElfinder()
    {
        $this->line('Configure Laravel Elfinder');
        $this->executeCommand(['php', 'artisan', 'vendor:publish', '--provider', 'Barryvdh\Elfinder\ElfinderServiceProvider']);

        /**
         * Keep only tinymce5 bridge which is the only used by Vuetify Admin
         */
        foreach ($this->files->allFiles(resource_path('views/vendor/elfinder')) as $file) {
            if ($file->getFilename() !== 'tinymce5.blade.php') {
                unlink($file);
            }
        }

        /**
         * Do not include public packages
         */
        $this->addToGitIgnore('/public/packages');

        /**
         * Publish Elfinder assets
         */
        $this->executeCommand(['php', 'artisan', 'elfinder:publish']);
    }

    private function configureLaravelIdeHelper()
    {
        $this->line('Configure Laravel IDE Helper');

        $this->warn(
            'Add this code inside composer.json for automatic generation :' . <<<EOF
"scripts":{
    "post-update-cmd": [
        "Illuminate\\Foundation\\ComposerScripts::postUpdate",
        "@php artisan ide-helper:generate",
        "@php artisan ide-helper:meta"
    ]
},
EOF
        );

        /**
         * Do not include generated code
         */
        $this->addToGitIgnore('.phpstorm.meta.php');
        $this->addToGitIgnore('_ide_helper.php');
    }

    private function configurePhpCsFixer()
    {
        $this->line('Configure PHP CS Fixer');

        $this->call('vendor:publish', [
            '--provider' => AdminServiceProvider::class,
            '--tag' => 'phpcs',
        ]);

        /**
         * Do not include phpcs cache
         */
        $this->addToGitIgnore('.php_cs.cache');
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
        $this->call('vendor:publish', [
            '--provider' => AdminServiceProvider::class,
            '--tag' => 'docker',
        ]);

        $this->line("\nUse this docker variables into you .env :");

        $this->warn(
            <<<EOF
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

SANCTUM_STATEFUL_DOMAINS=localhost:8080

DB_HOST=mysql
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel

REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

DOCKER_PUID=1000
DOCKER_PGID=1000
NGINX_HTTP_PORT=8000
PMA_PORT=9000
MYSQL_ROOT_PASSWORD=root
EOF
        );
    }

    private function installDependencies(array $dependencies, bool $dev = false)
    {
        $this->line($dev ? 'Add dev dependencies' : 'Add dependencies');

        $command = array_merge(['composer', 'require'], $dependencies, ['--no-update']);

        if ($dev) {
            $command[] = '--dev';
        }

        $process = new Process($command, null, null, null, null);
        $process->run();
    }

    private function executeCommand($command)
    {
        $process = new Process($command, null, null, null, null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->comment($buffer);
            } else {
                $this->line($buffer);
            }
        });
    }

    private function addToGitIgnore($line)
    {
        if (! Str::contains($this->files->get(base_path('.gitignore')), $line)) {
            $this->files->append(base_path('.gitignore'), "$line\n");
        }
    }
}
