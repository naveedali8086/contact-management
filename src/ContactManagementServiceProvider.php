<?php

namespace Naveedali8086\ContactManagement;

use Illuminate\Support\ServiceProvider;
use Naveedali8086\ContactManagement\Console\InstallCommand;

class ContactManagementServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class
        ]);
    }

    public function provides()
    {
        return [InstallCommand::class];
    }
}