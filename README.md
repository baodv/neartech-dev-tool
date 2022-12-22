# Neartech Dev Tools
neartech/dev-tool is a Laravel package which wa created to manage your Laravel
. Module is like a laravel package, it have some views, controllers or models.
This package is supported available tools for HMVC Laravel projects.

### Installation

To install through composer, simply put the following in your `composer.json` file:

```$xslt
{
    "repositories": [
        {
            "type": "path",
            "url": "./platform/core/*"
        },
        {
            "type": "path",
            "url": "./platform/plugins/*"
        },
        {
            "type": "path",
            "url": "./platform/packages/*"
        }
    ]
}
```
And then run composer install to fetch the package.

```$xslt
composer require neartech/dev-tool
```
### Create new module
To create a new module you can simply run :
```$xslt
php artisan neartech:module:create <module-name> <--autoload>
```
- `<module-name>` - Required. The name of module will be created.
- `<--autoload>`  - Optional. You might need to register your module provider to `config/app.php` if you skip option.

## Artisan Commands
Update later
