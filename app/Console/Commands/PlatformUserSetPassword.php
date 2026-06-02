<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PlatformUserSetPassword extends Command
{
    protected $signature = 'platform:user:set-password {email} {password}';

    protected $description = 'Set a password for a user, enabling platform Basic Auth access';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) $this->argument('password');

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("No user found with email {$email}");
            return self::FAILURE;
        }

        $user->password = $password;
        $user->save();

        $this->info("Password updated for {$email}.");

        return self::SUCCESS;
    }
}
