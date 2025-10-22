# Hypervel Module Commands

A Composer package that provides commands for creating modules in Hypervel framework.

## Features

- **Make Module Command**: Create new modules as Composer packages
- **Stub-based Templates**: Uses stub files for clean, maintainable templates
- **Auto-discovery**: Automatically registers service providers
- **Complete Structure**: Creates full module structure with all necessary directories
- **Multiple Commands**: Create modules, controllers, migrations, and models

## Installation

### Method 1: Local Development Package

Add to your `composer.json`:

```json
{
    "require": {
        "moaladdin/hypervel-modules": "*"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./packages/moaladdin/hypervel-modules",
            "options": {
                "symlink": true
            }
        }
    ],
    "extra": {
        "merge-plugin": {
            "include": [
                "modules/*/composer.json"
            ]
        }
    }
}
```

**Important Notes**:
- The service provider is automatically registered via the package's `composer.json` configuration. No manual registration needed!
- The `merge-plugin` configuration is required to automatically include module dependencies from their individual `composer.json` files

### Method 2: Composer Repository (Future)

```bash
composer require moaladdin/hypervel-modules
```

## Commands Available

- `php artisan make:module ModuleName` - Create a new module
- `php artisan make:module-controller ModuleName ControllerName` - Create a controller
- `php artisan make:module-migration ModuleName MigrationName` - Create a migration
- `php artisan make:module-model ModuleName ModelName` - Create a model

## Usage

### Create a New Module

```bash
php artisan make:module ModuleName
```

This will create:
- Complete module structure
- `composer.json` with proper package configuration
- Service provider with auto-discovery
- Default controllers, models, middleware, and routes
- Configuration files

### Create a Controller

```bash
php artisan make:module-controller ModuleName ControllerName --resource --api
```

Options:
- `--resource` - Create a resource controller with CRUD methods
- `--api` - Create an API controller (returns JSON responses)

### Create a Migration

```bash
php artisan make:module-migration ModuleName MigrationName --create=table_name
```

Options:
- `--create=table_name` - Create a new table
- `--table=table_name` - Modify an existing table

### Create a Model

```bash
php artisan make:module-model ModuleName ModelName --migration --factory --seeder
```

Options:
- `--migration` - Create a migration for the model
- `--factory` - Create a factory for the model
- `--seeder` - Create a seeder for the model

## Generated Structure

```
modules/ModuleName/
├── composer.json                    # Composer package configuration
├── config/
│   └── modulename.php              # Module configuration
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   └── Factories/
├── Http/
│   ├── Controllers/
│   │   └── ApiController.php       # Default API controller
│   ├── Middleware/
│   │   └── ModuleNameMiddleware.php
│   └── Requests/
├── Models/
│   └── ModuleName.php             # Default model
├── Routes/
│   ├── api.php                    # API routes
│   └── web.php                    # Web routes
├── Providers/
│   └── ModuleNameServiceProvider.php  # Service provider
├── Resources/
│   └── lang/
├── Events/
├── Listeners/
├── Jobs/
├── Mail/
├── Notifications/
└── Services/
```

## Stub Files

The package uses stub files for templates, making them easy to customize:

- `stubs/service-provider.stub` - Service provider template
- `stubs/api-controller.stub` - API controller template
- `stubs/controller-basic.stub` - Basic controller template
- `stubs/controller-resource.stub` - Resource controller template
- `stubs/model.stub` - Model template
- `stubs/middleware.stub` - Middleware template
- `stubs/api-routes.stub` - API routes template
- `stubs/config.stub` - Configuration template
- `stubs/migration-create.stub` - Create migration template
- `stubs/migration-modify.stub` - Modify migration template
- `stubs/migration-generic.stub` - Generic migration template
- `stubs/factory.stub` - Factory template
- `stubs/seeder.stub` - Seeder template

## Customization

You can customize the stub files in the `stubs/` directory to modify the generated code templates.

## Auto-Registration

The package service provider is automatically registered via the `extra.hypervel.providers` configuration in the package's `composer.json`. Each generated module's service provider is also automatically registered via the same mechanism.

## Merge Plugin Configuration

The `merge-plugin` configuration is essential for the module system to work properly. It allows Composer to automatically include dependencies from each module's individual `composer.json` file.

**Required Configuration**:
```json
"extra": {
    "merge-plugin": {
        "include": [
            "modules/*/composer.json"
        ]
    }
}
```

This ensures that:
- Module dependencies are automatically installed
- Module service providers are discovered
- Module autoloading works correctly

## Post-Installation Steps

After creating a module, run:

```bash
composer dump-autoload
```

This ensures the new module's classes are properly autoloaded.

## Benefits

- **Modular Architecture**: Each module is a separate Composer package
- **Auto-Registration**: Service providers are automatically registered via Composer
- **Standard Structure**: Consistent module structure across projects
- **Easy Distribution**: Modules can be distributed as Composer packages
- **Namespace Isolation**: Each module has its own namespace
- **Resource Management**: Automatic loading of all module resources
- **Stub-based Templates**: Easy customization of generated code
- **Multiple Commands**: Comprehensive module development tools

## Requirements

- PHP >= 8.2
- Hypervel Framework ^0.3
- Composer

## License

MIT License
