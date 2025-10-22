<?php

declare(strict_types=1);

namespace MoAladdin\HypervelModules;

use Hypervel\Support\ServiceProvider;
use MoAladdin\HypervelModules\Commands\MakeModuleCommand;
use MoAladdin\HypervelModules\Commands\MakeModuleControllerCommand;
use MoAladdin\HypervelModules\Commands\MakeModuleMigrationCommand;
use MoAladdin\HypervelModules\Commands\MakeModuleModelCommand;

class ModuleCommandsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            MakeModuleCommand::class,
            MakeModuleControllerCommand::class,
            MakeModuleMigrationCommand::class,
            MakeModuleModelCommand::class,
        ]);
    }

    public function register(): void
    {
        // Register any services here
    }
}
