<?php

declare(strict_types=1);

namespace MoAladdin\HypervelModules\Commands;

use Hypervel\Console\Command;
use Hypervel\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected ?string $signature = 'make:module {name : The name of the module}';
    protected string $description = 'Create a new module with complete structure';

    protected string $stubPath;

    public function __construct()
    {
        parent::__construct();
        $this->stubPath = __DIR__ . '/../../stubs/';
    }

    public function handle(): int
    {
        $moduleName = $this->argument('name');
        $modulePath = base_path("modules/{$moduleName}");

        if (is_dir($modulePath)) {
            $this->error("Module {$moduleName} already exists!");
            return 1;
        }

        $this->info("Creating module: {$moduleName}");

        $this->createModuleStructure($moduleName, $modulePath);
        $this->createComposerJson($moduleName, $modulePath);
        $this->createServiceProvider($moduleName, $modulePath);
        $this->createRoutes($moduleName, $modulePath);
        $this->createConfig($moduleName, $modulePath);
        $this->createControllers($moduleName, $modulePath);
        $this->createModels($moduleName, $modulePath);
        $this->createMiddleware($moduleName, $modulePath);

        $this->info("Module {$moduleName} created successfully!");
        $this->line("Module path: {$modulePath}");
        $this->line("Don't forget to run: composer dump-autoload");

        return 0;
    }

    protected function createModuleStructure(string $moduleName, string $modulePath): void
    {
        $directories = [
            'Http/Controllers',
            'Http/Middleware',
            'Http/Requests',
            'Models',
            'Database/Migrations',
            'Database/Seeders',
            'Database/Factories',
            'Routes',
            'Providers',
            'config',
            'Resources/lang',
            'Events',
            'Listeners',
            'Jobs',
            'Mail',
            'Notifications',
            'Services',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory($modulePath . '/' . $dir, 0755, true);
        }

        $this->line("✓ Created module directory structure");
    }

    protected function createComposerJson(string $moduleName, string $modulePath): void
    {
        $composerContent = $this->getComposerJsonTemplate($moduleName);
        File::put($modulePath . '/composer.json', $composerContent);
        $this->line("✓ Created composer.json");
    }

    protected function createServiceProvider(string $moduleName, string $modulePath): void
    {
        $providerContent = $this->getStubContent('service-provider.stub', [
            'MODULE_NAME' => $moduleName,
        ]);
        File::put($modulePath . "/Providers/{$moduleName}ServiceProvider.php", $providerContent);
        $this->line("✓ Created service provider");
    }

    protected function createRoutes(string $moduleName, string $modulePath): void
    {
        $apiRoutes = $this->getStubContent('api-routes.stub', [
            'MODULE_NAME' => $moduleName,
        ]);
        File::put($modulePath . '/Routes/api.php', $apiRoutes);
        $this->line("✓ Created route files");
    }

    protected function createConfig(string $moduleName, string $modulePath): void
    {
        $configContent = $this->getStubContent('config.stub', [
            'MODULE_NAME' => $moduleName,
        ]);
        File::put($modulePath . "/config/" . strtolower($moduleName) . '.php', $configContent);
        $this->line("✓ Created config file");
    }

    protected function createControllers(string $moduleName, string $modulePath): void
    {
        $apiController = $this->getStubContent('api-controller.stub', [
            'MODULE_NAME' => $moduleName,
        ]);
        File::put($modulePath . "/Http/Controllers/ApiController.php", $apiController);
        $this->line("✓ Created default controllers");
    }

    protected function createModels(string $moduleName, string $modulePath): void
    {
        $modelName = rtrim($moduleName, 's'); // Remove 's' if plural
        $modelContent = $this->getStubContent('model.stub', [
            'MODULE_NAME' => $moduleName,
            'MODEL_NAME' => $modelName,
            'TABLE_NAME' => strtolower($modelName) . 's',
        ]);
        File::put($modulePath . "/Models/{$modelName}.php", $modelContent);
        $this->line("✓ Created default model");
    }

    protected function createMiddleware(string $moduleName, string $modulePath): void
    {
        $middlewareContent = $this->getStubContent('middleware.stub', [
            'MODULE_NAME' => $moduleName,
        ]);
        File::put($modulePath . "/Http/Middleware/{$moduleName}Middleware.php", $middlewareContent);
        $this->line("✓ Created middleware");
    }

    protected function getStubContent(string $stubName, array $replacements = []): string
    {
        $stubPath = $this->stubPath . $stubName;
        
        if (!file_exists($stubPath)) {
            throw new \Exception("Stub file not found: {$stubPath}");
        }

        $content = file_get_contents($stubPath);

        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{" . $search . "}}", $replace, $content);
        }

        return $content;
    }

    protected function getComposerJsonTemplate(string $moduleName): string
    {
        $packageName = strtolower($moduleName);
        return json_encode([
            'name' => "hypervel/module-{$packageName}",
            'type' => 'library',
            'description' => "{$moduleName} module for Hypervel framework",
            'keywords' => [
                'hypervel',
                'module',
                strtolower($moduleName)
            ],
            'license' => 'MIT',
            'authors' => [
                [
                    'name' => 'Your Name',
                    'email' => 'your.email@example.com'
                ]
            ],
            'require' => [
                'php' => '>=8.2',
                'hypervel/framework' => '^0.3'
            ],
            'autoload' => [
                'psr-4' => [
                    "App\\Modules\\{$moduleName}\\" => ''
                ]
            ],
            'extra' => [
                'hypervel' => [
                    'providers' => [
                        "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider"
                    ]
                ]
            ],
            'config' => [
                'sort-packages' => true
            ],
            'minimum-stability' => 'dev',
            'prefer-stable' => true
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
