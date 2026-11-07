<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = config('initial_user.email');
        $password = config('initial_user.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            throw new RuntimeException('SEEDER_USER_EMAIL and SEEDER_USER_PASSWORD must be configured.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'User',
                'password' => $password,
            ],
        );
    }
}
