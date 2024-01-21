<?php

namespace Naveedali8086\ContactManagement\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'contact-crud:install';

    protected $description = 'Install the CRUD functionality for contacts management';

    public function handle()
    {
        $fileSystem = new Filesystem();

        // Copy controllers
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Http/Controllers', app_path('Http/Controllers'));

        // Copy requests
        $fileSystem->ensureDirectoryExists(app_path('Http/Requests'));
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Http/Requests', app_path('Http/Requests'));

        // Copy models
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Models', app_path('Models'));

        // Copy enums
        $fileSystem->ensureDirectoryExists(app_path('Enums'));
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Enums', app_path('Enums'));

        // Copy rules
        $fileSystem->ensureDirectoryExists(app_path('Rules'));
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Rules', app_path('Rules'));

        // copy factories
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/database/factories', base_path('database/factories'));

        // copy seeders
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/database/seeders', base_path('database/seeders'));

        // copy migrations
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/database/migrations', base_path('database/migrations'));

        // Copy policies
        $fileSystem->ensureDirectoryExists(app_path('Policies'));
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Policies', app_path('Policies'));

        // Copy resources
        $fileSystem->ensureDirectoryExists(app_path('Http/Resources'));
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/app/Http/Resources', app_path('Http/Resources'));

        // copy tests
        $fileSystem->copyDirectory(__DIR__ . '/../../stubs/tests/Feature', base_path('tests/Feature'));

        $this->info("contact-management scaffolding installed successfully");
    }
}