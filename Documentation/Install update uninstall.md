Install a bundle
================

You can use SF2 console :
	
```php
php app/console bundle:install MyBundle
php app/console bundle:install:all
```

Or you can use Installer service :
	
```php
$container->get('bundle.installer')->install('MyBundle');
```

Update a bundle
===============

You can use SF2 console :
	
```php
php app/console bundle:upate MyBundle
php app/console bundle:upate:all
```

Or you can use Installer service :
	
```php
$container->get('bundle.installer')->update('MyBundle');
```

Uninstall a bundle
==================

You can use SF2 console :
	
```php
php app/console bundle:uninstall MyBundle
php app/console bundle:uninstall MyBundle --force
```

Or you can use Installer service :
	
```php
$container->get('bundle.installer')->uninstall('MyBundle');
```