<?php

declare(strict_types=1);

namespace MoAladdin\HypervelModules\Commands;

use Hypervel\Console\Command;
use Hypervel\Support\Facades\File;

class MakeModuleMigrationCommand extends Command
{
    protected ?string $signature = 'make:module-migration {module : The module name} {name : The migration name} {--create= : The table to create} {--table= : The table to modify} {--namespace=App\\Modules : The namespace for the module}';
    protected string $description = 'Create a migration for a specific module';

    protected string $stubPath;

    public function __construct()
    {
        parent::__construct();
        $this->stubPath = __DIR__ . '/../../stubs/';
    }

    public function handle(): int
    {
        $moduleName = $this->argument('module');
        $migrationName = $this->argument('name');
        $modulePath = base_path("modules/{$moduleName}");

        if (!is_dir($modulePath)) {
            $this->error("Module {$moduleName} does not exist!");
            $this->line("Create the module first using: php artisan make:module {$moduleName}");
            return 1;
        }

        $migrationPath = $modulePath . '/src/Database/Migrations';
        if (!is_dir($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $className = $this->getMigrationClassName($migrationName);
        $fileName = "{$timestamp}_{$migrationName}.php";
        $filePath = $migrationPath . "/{$fileName}";

        $createTable = $this->option('create');
        $modifyTable = $this->option('table');

        $migrationContent = $this->getMigrationTemplate($className, $migrationName, $createTable, $modifyTable);
        File::put($filePath, $migrationContent);

        $this->info("Migration created successfully!");
        $this->line("File: {$fileName}");
        $this->line("Path: {$filePath}");

        if ($createTable) {
            $this->line("This migration will create the '{$createTable}' table.");
        } elseif ($modifyTable) {
            $this->line("This migration will modify the '{$modifyTable}' table.");
        }

        return 0;
    }

    protected function getMigrationClassName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    protected function getMigrationTemplate(string $className, string $name, ?string $createTable, ?string $modifyTable): string
    {
        $tableName = $createTable ?? $modifyTable ?? 'table_name';
        
        if ($createTable) {
            return $this->getStubContent('migration-create.stub', [
                'CLASS_NAME' => $className,
                'TABLE_NAME' => $createTable,
            ]);
        } elseif ($modifyTable) {
            return $this->getStubContent('migration-modify.stub', [
                'CLASS_NAME' => $className,
                'TABLE_NAME' => $modifyTable,
            ]);
        } else {
            return $this->getStubContent('migration-generic.stub', [
                'CLASS_NAME' => $className,
            ]);
        }
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
