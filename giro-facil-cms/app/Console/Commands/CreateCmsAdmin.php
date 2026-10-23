<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateCmsAdmin extends Command
{
    protected $signature = 'cms:create-admin';
    protected $description = 'Cria uma conta administradora do CMS';

    public function handle(): int
    {
        $name = $this->ask('Nome do administrador');
        $email = $this->ask('Email');
        $password = $this->secret('Palavra-passe (mínimo 12 caracteres)');

        if (! $name || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $password || strlen($password) < 12) {
            $this->error('Indique nome, email válido e uma palavra-passe com pelo menos 12 caracteres.');

            return self::FAILURE;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'is_admin' => true],
        );

        $this->info("Administrador {$email} criado/actualizado.");

        return self::SUCCESS;
    }
}
