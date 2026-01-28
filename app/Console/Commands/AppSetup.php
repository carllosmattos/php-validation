<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AppSetup extends Command
{
    protected $signature = 'app:setup';
    protected $description = 'Initial setup for local development environment';

    public function handle(): int
    {
        $this->info('🚀 Starting application setup...');

        // 1. .env
        if (!File::exists(base_path('.env'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
            $this->info('✔ .env file created');
        } else {
            $this->line('ℹ .env already exists');
        }

        // 2. APP_KEY
        if (empty(config('app.key'))) {
            $this->callSilent('key:generate');
            $this->info('✔ Application key generated');
        } else {
            $this->line('ℹ APP_KEY already set');
        }

        // 3. Sanctum
        if (!File::exists(config_path('sanctum.php'))) {
            $this->callSilent('vendor:publish', [
                '--provider' => 'Laravel\Sanctum\SanctumServiceProvider',
            ]);
            $this->info('✔ Sanctum published');
        } else {
            $this->line('ℹ Sanctum already published');
        }

        // 4. Migrations
        $this->call('migrate', ['--force' => true]);

        // 5. Permissions (dev only)
        if (app()->environment('local')) {
            $this->info('✔ Fixing storage permissions (local only)');
            exec('chmod -R 777 storage bootstrap/cache');
        }

        $this->info('✅ Setup completed successfully!');

        return self::SUCCESS;
    }
}
