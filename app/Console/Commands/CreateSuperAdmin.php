<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('create:super-admin {email? : The email address of the super admin} {--name= : The name of the super admin} {--email= : The email address of the super admin} {--password= : The password for the super admin}')]
#[Description('Create a new super admin user or promote an existing user to super admin')]
class CreateSuperAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Create Super Admin');
        $this->line('----------------------------------------');

        $email = $this->argument('email') ?? $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password');

        // 1. Get or prompt for Email
        if (empty($email)) {
            $email = (string) $this->ask('Email address');
        }

        $email = trim($email);

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            $this->error('The provided email address is invalid.');

            return self::FAILURE;
        }

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            $roleLabel = $user->role instanceof UserRole ? $user->role->label() : (string) $user->role;
            $this->warn("A user with email [{$email}] already exists with role [{$roleLabel}].");

            if (! $this->input->isInteractive()) {
                $this->error('Cannot proceed non-interactively when a user already exists with this email.');

                return self::FAILURE;
            }

            $action = $user->isSuperAdmin()
                ? 'reset their password'
                : 'promote them to Super Admin';

            if (! $this->confirm("Do you want to {$action}?", true)) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }

            if (empty($password)) {
                $password = (string) $this->secret('New password (min. 8 characters)');
                $confirmPassword = (string) $this->secret('Confirm new password');

                if ($password !== $confirmPassword) {
                    $this->error('Passwords do not match.');

                    return self::FAILURE;
                }
            }

            if (strlen($password) < 8) {
                $this->error('Password must be at least 8 characters.');

                return self::FAILURE;
            }

            $user->role = UserRole::SuperAdmin;
            $user->studio_id = null;
            $user->is_active = true;
            $user->password = $password;

            if ($name) {
                $user->name = $name;
            }

            $user->save();

            $this->newLine();
            $this->info("User [{$email}] has been updated to Super Admin successfully.");
            $this->displaySummary($user);

            return self::SUCCESS;
        }

        // 2. Get or prompt for Name
        if (empty($name)) {
            $name = (string) $this->ask('Full Name', 'Super Admin');
        }

        $name = trim($name);

        if (empty($name)) {
            $this->error('Name is required.');

            return self::FAILURE;
        }

        // 3. Get or prompt for Password
        if (empty($password)) {
            $password = (string) $this->secret('Password (min. 8 characters)');
            $confirmPassword = (string) $this->secret('Confirm password');

            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        // 4. Create the Super Admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::SuperAdmin,
            'studio_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info('Super Admin created successfully!');
        $this->displaySummary($user);

        return self::SUCCESS;
    }

    private function displaySummary(User $user): void
    {
        $roleLabel = $user->role instanceof UserRole ? $user->role->label() : (string) $user->role;

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Status'],
            [[
                $user->id,
                $user->name,
                $user->email,
                $roleLabel,
                $user->is_active ? 'Active' : 'Inactive',
            ]]
        );

        $this->line('Login URL: <comment>'.url('/login').'</comment>');
    }
}

