<?php

namespace Okami101\LaravelAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class UICommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'admin:ui';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold Vue CLI Admin project';

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
        if (! $this->isVueCLIInstalled()) {
            return;
        }

        $dir = $this->ask('Sub folder for Vue CLI Admin project ?', 'admin');
        $this->install($dir);
    }

    private function isVueCLIInstalled()
    {
        $process = new Process(['vue']);
        $code = $process->run(function ($type) {
            if (Process::ERR === $type) {
                $this->comment('You must install Vue CLI, follow https://cli.vuejs.org/guide/installation.html');
            }
        });

        return $code === 0;
    }

    private function install($dir)
    {
        $process = new Process(['vue', 'create', $dir, '--no-git', '--preset', __DIR__ . '/../../preset.json'], null, null, null, null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->comment($buffer);
            } else {
                $this->line($buffer);
            }
        });
    }
}
