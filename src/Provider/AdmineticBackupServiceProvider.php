<?php

namespace Adminetic\Backup\Provider;

use Adminetic\Backup\Http\Livewire\AdmineticBackup;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AdmineticBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishResource();
        }
        // Register Resources
        $this->registerResource();
        // Register View Components
        $this->registerLivewireComponents();
    }

    /**
     * Publish Package Resource.
     *
     *@return void
     */
    protected function publishResource()
    {
        // Publish Config File
        $this->publishes([
            __DIR__.'/../../config/adminetic-backup.php' => config_path('adminetic-backup.php'),
        ], 'backup-config');
    }

    /**
     * Register Package Resource.
     *
     *@return void
     */
    protected function registerResource()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'backup'); // Loading Views Files
    }

    /**
     * Register Components.
     *
     *@return void
     */
    protected function registerLivewireComponents()
    {
        Livewire::component('adminetic.backup', AdmineticBackup::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/adminetic-backup.php', 'adminetic-backup');
    }
}
