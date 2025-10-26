<?php

declare(strict_types=1);

namespace MoAladdin\HypervelModules\Commands;

use Hypervel\Console\Command;
use Hypervel\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected ?string $signature = 'make:module {name : The name of the module} {--namespace=App\\Modules : The namespace for the module}';
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
        $namespace = $this->option('namespace');
        $modulePath = base_path("modules/{$moduleName}");

        if (is_dir($modulePath)) {
            $this->error("Module {$moduleName} already exists!");
            return 1;
        }

        $this->info("Creating module: {$moduleName}");
        $this->info("Using namespace: {$namespace}\\{$moduleName}");

        $this->createModuleStructure($moduleName, $modulePath);
        $this->createComposerJson($moduleName, $modulePath, $namespace);
        $this->createServiceProvider($moduleName, $modulePath, $namespace);
        $this->createRoutes($moduleName, $modulePath, $namespace);
        $this->createConfig($moduleName, $modulePath);
        $this->createControllers($moduleName, $modulePath, $namespace);
        $this->createModels($moduleName, $modulePath, $namespace);
        $this->createMiddleware($moduleName, $modulePath, $namespace);

        $this->info("Module {$moduleName} created successfully!");
        $this->line("Module path: {$modulePath}");
        $this->line("Don't forget to run: composer dump-autoload");

        return 0;
    }

    protected function createModuleStructure(string $moduleName, string $modulePath): void
    {
        $directories = [
            'src/Http/Controllers',
            'src/Http/Middleware',
            'src/Http/Requests',
            'src/Models',
            'src/Database/Migrations',
            'src/Database/Seeders',
            'src/Database/Factories',
            'src/Routes',
            'src/Providers',
            'config',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory($modulePath . '/' . $dir, 0755, true);
        }

        $this->line("✓ Created module directory structure");
    }

    protected function createComposerJson(string $moduleName, string $modulePath, string $namespace): void
    {
        $composerContent = $this->getComposerJsonTemplate($moduleName, $namespace);
        File::put($modulePath . '/composer.json', $composerContent);
        $this->line("✓ Created composer.json");
    }

    protected function createServiceProvider(string $moduleName, string $modulePath, string $namespace): void
    {
        $providerContent = $this->getStubContent('service-provider.stub', [
            'MODULE_NAME' => $moduleName,
            'NAMESPACE' => $namespace,
        ]);
        File::put($modulePath . "/src/Providers/{$moduleName}ServiceProvider.php", $providerContent);
        $this->line("✓ Created service provider");
    }

    protected function createRoutes(string $moduleName, string $modulePath, string $namespace): void
    {
        $apiRoutes = $this->getStubContent('api-routes.stub', [
            'MODULE_NAME' => $moduleName,
            'NAMESPACE' => $namespace,
        ]);
        File::put($modulePath . '/src/Routes/api.php', $apiRoutes);
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

    protected function createControllers(string $moduleName, string $modulePath, string $namespace): void
    {
        $apiController = $this->getStubContent('api-controller.stub', [
            'MODULE_NAME' => $moduleName,
            'NAMESPACE' => $namespace,
        ]);
        File::put($modulePath . "/src/Http/Controllers/ApiController.php", $apiController);
        $this->line("✓ Created default controllers");
    }

    protected function createModels(string $moduleName, string $modulePath, string $namespace): void
    {
        // For module names ending with 's', remove it to get singular form
        // For others, use the module name as is
        $modelName = $moduleName;
        if (str_ends_with($moduleName, 's') && strlen($moduleName) > 1) {
            $modelName = rtrim($moduleName, 's');
        }
        
        $modelContent = $this->getStubContent('model.stub', [
            'MODULE_NAME' => $moduleName,
            'MODEL_NAME' => $modelName,
            'TABLE_NAME' => strtolower($modelName) . 's',
            'NAMESPACE' => $namespace,
        ]);
        File::put($modulePath . "/src/Models/{$modelName}.php", $modelContent);
        $this->line("✓ Created default model");
    }

    protected function createMiddleware(string $moduleName, string $modulePath, string $namespace): void
    {
        $middlewareContent = $this->getStubContent('middleware.stub', [
            'MODULE_NAME' => $moduleName,
            'NAMESPACE' => $namespace,
        ]);
        File::put($modulePath . "/src/Http/Middleware/{$moduleName}Middleware.php", $middlewareContent);
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

    protected function getComposerJsonTemplate(string $moduleName, string $namespace): string
    {
        $packageName = strtolower($moduleName);
        $fullNamespace = "{$namespace}\\{$moduleName}";
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
                    "{$fullNamespace}\\" => 'src/'
                ]
            ],
            'extra' => [
                'hypervel' => [
                    'providers' => [
                        "{$fullNamespace}\\Providers\\{$moduleName}ServiceProvider"
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
