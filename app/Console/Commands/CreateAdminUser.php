<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create-user')]
#[Description('Create or update a local administrator account.')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $name = text('Administrator name', required: true);
        $email = text('Administrator email', required: true, validate: fn (string $value): ?string => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.'
        );
        $newPassword = password('Administrator password (at least 12 characters)', required: true, validate: function (string $value): ?string {
            return strlen($value) >= 12 ? null : 'Use at least 12 characters.';
        });

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($newPassword),
                'email_verified_at' => now(),
            ],
        );

        $this->components->info('Administrator account saved.');

        return self::SUCCESS;
    }
}
