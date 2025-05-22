## Requirements

* PHP 8.2
* Symfony >=7.2

### Install the bundle

Execute the following [composer](https://getcomposer.org/) command

```bash
composer require alengo/alengo-webspace-settings-bundle
```


### Enable the bundle

Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

 ```php
 return [
     /* ... */
     Alengo\Bundle\AlengoWebspaceSettingsBundle\AlengoWebspaceSettingsBundle::class => ['all' => true],
 ];
 ```

```bash
bin/console do:sch:up --force
```


### Configure the Bundle

Set the following config in your routes_admin.yaml

 ```yaml
app_webspace_settings_api:
    type: rest
    resource: Alengo\Bundle\AlengoWebspaceSettingsBundle\Controller\Admin\WebspaceSettingsController
    prefix: /admin/api
    name_prefix: app.
 ```