<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create {email : The admin email address} {--name= : The admin name} {--password= : Optional password for non-interactive creation}', function (string $email) {
    $user = User::query()->where('email', $email)->first();

    if ($user) {
        $user->forceFill(['is_admin' => true])->save();
        $this->info("Admin access granted to {$user->email}.");

        return 0;
    }

    $name = $this->option('name') ?: $this->ask('Name', Str::before($email, '@'));
    $password = $this->option('password');

    if (! $password) {
        $password = $this->secret('Password');
        $confirmation = $this->secret('Confirm password');

        if ($password !== $confirmation) {
            $this->error('Passwords do not match.');

            return 1;
        }
    }

    if (! $password || strlen($password) < 8) {
        $this->error('Password must be at least 8 characters.');

        return 1;
    }

    $user = User::query()->create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'is_admin' => true,
    ]);

    $this->info("Admin user created: {$user->email}.");

    return 0;
})->purpose('Create a Nova admin user or grant admin access to an existing user');
