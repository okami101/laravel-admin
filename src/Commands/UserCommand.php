<?php

namespace Okami101\LaravelAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\InputArgument;

class UserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'admin:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');

        $validator = Validator::make([
            'email' => $email,
        ], [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return;
        }

        $name = $this->ask('Give the username');
        $password = $this->secret('Give the password');

        $model = config('auth.providers.users.model');

        /** @var Model $user */
        $user = $model::make([
            'name' => $name,
            'email' => $email,
        ]);

        $user->password = Hash::make($password);
        $user->save();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['email', InputArgument::REQUIRED, 'The email of the user'],
        ];
    }
}
