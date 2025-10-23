<?php

declare(strict_types=1);

namespace MoAladdin\HypervelModules\Commands;

use Hypervel\Console\Command;
use Hypervel\Support\Facades\File;

class MakeModuleModelCommand extends Command
{
    protected ?string $signature = 'make:module-model {module : The module name} {name : The model name} {--migration : Create a migration for the model} {--factory : Create a factory for the model} {--seeder : Create a seeder for the model} {--namespace=App\\Modules : The namespace for the module}';
    protected string $description = 'Create a model for a specific module';

    protected string $stubPath;

    public function __construct()
    {
        parent::__construct();
        $this->stubPath = __DIR__ . '/../../stubs/';
    }

    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $modelName = $this->argument('name');
        $namespace = $this->option('namespace');
        $modulePath = base_path("modules/{$moduleName}");

        if (!is_dir($modulePath)) {
            $this->error("Module {$moduleName} does not exist!");
            $this->line("Create the module first using: php artisan make:module {$moduleName}");
            return 1;
        }

        $modelPath = $modulePath . '/src/Models';
        if (!is_dir($modelPath)) {
            File::makeDirectory($modelPath, 0755, true);
        }

        $className = $this->getModelClassName($modelName);
        $fileName = "{$className}.php";
        $filePath = $modelPath . "/{$fileName}";

        $modelContent = $this->getModelTemplate($moduleName, $className, $namespace);
        File::put($filePath, $modelContent);

        $this->info("Model created successfully!");
        $this->line("File: {$fileName}");
        $this->line("Path: {$filePath}");

        // Create migration if requested
        if ($this->option('migration')) {
            $this->call('make:module-migration', [
                'module' => $moduleName,
                'name' => 'create_' . strtolower($className) . 's_table',
                '--create' => strtolower($className) . 's'
            ]);
        }

        // Create factory if requested
        if ($this->option('factory')) {
            $this->createFactory($moduleName, $className, $modulePath, $namespace);
        }

        // Create seeder if requested
        if ($this->option('seeder')) {
            $this->createSeeder($moduleName, $className, $modulePath, $namespace);
        }

        return 0;
    }

    protected function getModelClassName(string $name): string
    {
        return ucfirst(rtrim($name, 's')); // Remove 's' if plural and capitalize
    }

    protected function getModelTemplate(string $moduleName, string $className, string $namespace): string
    {
        return $this->getStubContent('model.stub', [
            'MODULE_NAME' => $moduleName,
            'CLASS_NAME' => $className,
            'TABLE_NAME' => strtolower($className) . 's',
            'NAMESPACE' => $namespace,
        ]);
    }

    protected function createFactory(string $moduleName, string $className, string $modulePath, string $namespace): void
    {
        $factoryPath = $modulePath . '/src/Database/Factories';
        if (!is_dir($factoryPath)) {
            File::makeDirectory($factoryPath, 0755, true);
        }

        $factoryName = "{$className}Factory.php";
        $factoryContent = $this->getFactoryTemplate($moduleName, $className, $namespace);
        File::put($factoryPath . "/{$factoryName}", $factoryContent);

        $this->line("✓ Created factory: {$factoryName}");
    }

    protected function createSeeder(string $moduleName, string $className, string $modulePath, string $namespace): void
    {
        $seederPath = $modulePath . '/src/Database/Seeders';
        if (!is_dir($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        $seederName = "{$className}Seeder.php";
        $seederContent = $this->getSeederTemplate($moduleName, $className, $namespace);
        File::put($seederPath . "/{$seederName}", $seederContent);

        $this->line("✓ Created seeder: {$seederName}");
    }

    protected function getFactoryTemplate(string $moduleName, string $className, string $namespace): string
    {
        return $this->getStubContent('factory.stub', [
            'MODULE_NAME' => $moduleName,
            'CLASS_NAME' => $className,
            'NAMESPACE' => $namespace,
        ]);
    }

    protected function getSeederTemplate(string $moduleName, string $className, string $namespace): string
    {
        return $this->getStubContent('seeder.stub', [
            'MODULE_NAME' => $moduleName,
            'CLASS_NAME' => $className,
            'NAMESPACE' => $namespace,
        ]);
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
}
