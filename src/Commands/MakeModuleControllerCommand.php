<?php

declare(strict_types=1);

namespace MoAladdin\HypervelModules\Commands;

use Hypervel\Console\Command;
use Hypervel\Support\Facades\File;

class MakeModuleControllerCommand extends Command
{
    protected ?string $signature = 'make:module-controller {module : The module name} {name : The controller name} {--resource : Generate a resource controller} {--api : Generate an API resource controller} {--namespace=App\\Modules : The namespace for the module}';
    protected string $description = 'Create a controller for a specific module';

    protected string $stubPath;

    public function __construct()
    {
        parent::__construct();
        $this->stubPath = __DIR__ . '/../../stubs/';
    }

    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $controllerName = $this->argument('name');
        $namespace = $this->option('namespace');
        $modulePath = base_path("modules/{$moduleName}");

        if (!is_dir($modulePath)) {
            $this->error("Module {$moduleName} does not exist!");
            $this->line("Create the module first using: php artisan make:module {$moduleName}");
            return 1;
        }

        $controllerPath = $modulePath . '/src/Http/Controllers';
        if (!is_dir($controllerPath)) {
            File::makeDirectory($controllerPath, 0755, true);
        }

        $className = $this->getControllerClassName($controllerName);
        $fileName = "{$className}.php";
        $filePath = $controllerPath . "/{$fileName}";

        $isResource = $this->option('resource');
        $isApi = $this->option('api');

        $controllerContent = $this->getControllerTemplate($moduleName, $className, $isResource, $isApi, $namespace);
        File::put($filePath, $controllerContent);

        $this->info("Controller created successfully!");
        $this->line("File: {$fileName}");
        $this->line("Path: {$filePath}");

        if ($isResource) {
            $this->line("This is a resource controller with CRUD methods.");
        } elseif ($isApi) {
            $this->line("This is an API resource controller with CRUD methods.");
        }

        return 0;
    }

    protected function getControllerClassName(string $name): string
    {
        $name = str_replace('Controller', '', $name);
        return ucfirst($name) . 'Controller';
    }

    protected function getControllerTemplate(string $moduleName, string $className, bool $isResource, bool $isApi, string $namespace): string
    {
        if ($isResource || $isApi) {
            return $this->getResourceControllerTemplate($moduleName, $className, $isApi, $namespace);
        }

        return $this->getBasicControllerTemplate($moduleName, $className, $namespace);
    }

    protected function getBasicControllerTemplate(string $moduleName, string $className, string $namespace): string
    {
        return $this->getStubContent('controller-basic.stub', [
            'MODULE_NAME' => $moduleName,
            'CLASS_NAME' => $className,
            'NAMESPACE' => $namespace,
        ]);
    }

    protected function getResourceControllerTemplate(string $moduleName, string $className, bool $isApi, string $namespace): string
    {
        $modelName = str_replace('Controller', '', $className);
        $modelName = rtrim($modelName, 's'); // Remove 's' if plural

        return $this->getStubContent('controller-resource.stub', [
            'MODULE_NAME' => $moduleName,
            'CLASS_NAME' => $className,
            'MODEL_NAME' => $modelName,
            'IS_API' => $isApi ? 'true' : 'false',
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
