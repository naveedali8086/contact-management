<?php

namespace Naveedali8086\ContactManagement\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'contact-crud:install';

    protected $description = 'Install the CRUD functionality for contacts management';

    public function handle()
    {
        $fileSystem = new Filesystem();

        // copy migrations
        $filePathsTo = File::glob(__DIR__ . '/../../stubs/database/migrations/*.php');
        if ($error = create_table_migration_exists(['contacts'])) {
            $this->info($error);
            return;
        }
        copy_migrations(database_path('migrations'), $filePathsTo);

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

    private function copyMigrations(): void
    {
        // Get the path where migrations should be published
        $destinationPath = database_path('migrations');

        // Get all migration files in the source directory
        $files = File::glob(__DIR__ . '/../../stubs/database/migrations/*.php');

        foreach ($files as $file) {
            // Extract the filename without extension
            $fileName = pathinfo($file, PATHINFO_FILENAME);

            // Append current date and time to the filename
            $timestamp = now()->format('Y_m_d_His');

            $newFilename = preg_replace('/\d{4}_\d{2}_\d{2}_\d+/', $timestamp, $fileName);

            // Publish the migration file with the new filename
            File::copy($file, "$destinationPath/$newFilename.php");
        }
    }

}