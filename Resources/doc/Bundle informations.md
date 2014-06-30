Get bundle version informations
===============================

You can use SF2 console :

```php
php app/console bundle:version MyBundle
```

Or you can use Version service :

```php
# will return an instance of kujaff\VersionsBundle\Model\VersionnedBundle
# getVersion() is bundle files version
# getInstalledVersion() is the installed version
# see VersionnedBundle for other methods
$container->get('versions.bundle')->getVersion('MyBundle');
```

Get all versionned bundles informations
=======================================

You can use SF2 console :

```php
php app/console bundle:list
```