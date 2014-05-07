VersionsBundle
==============

Add version information to your bundles, with install, updates, uninstall

Installation
============

Composer :
```json
# composer.json
{
    "require": {
        "kujaff/versionsbundle": "dev-master"
    }
}
```

Add bundle to your AppKernel :
```php
# app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // -----
            new kujaff\VersionsBundle\VersionsBundle(),
        );
    }
}
```

Add version type in your Doctrine config :
```yml
# app/config/config.yml
doctrine:
    dbal:
        types:
            version: kujaff\VersionsBundle\Versions\DoctrineType
```

Make your bundle versionned
============================

Make your bundle versionned by extending VersionnedBundle instead of Bundle :

```php
# MyBundle/MyBundle.php
use kujaff\VersionsBundle\Versions\VersionnedBundle;
use kujaff\VersionsBundle\Versions\Version;

class MyBundle extends VersionnedBundle
{
    public function __construct()
    {
        $this->version = new Version('1.0.0');
        # indicate if bundle needs to be installed or if it can be used without installation, true by default
        # a bundle not installed and required to be throws a kujaff\VersionsBundle\Versions\Exception at bundle boot
        $this->needInstallation = true;
        # indicate if bundle needs to be up to date or if it can be used without being up to date, true by default
        # a bundle not updated and required to be throws a kujaff\VersionsBundle\Versions\Exception at bundle boot
        $this->needUpToDate = true;
    }
}
```

Create an install script
========================

Declare a service with tag bundle.install :
```yml
# MyBundle/Resources/config/services.yml
services :
    mybundle.installer:
        class: MyBundle\Installer\Install
        tags:
            - { name: bundle.install }
```

Create the service who implements Install :
```php
# MyBundle/Installer/Install.php
namespace MyBundle/Installer;

use kujaff\VersionsBundle\Installer\Install as BaseInstall;
use kujaff\VersionsBundle\Versions\Version;

class Install implements BaseInstall
{
    public function getBundleName()
    {
        return 'MyBundle';
    }

    public function install()
    {
        // make stuff to install your bundle, like creating dirs, updating database schema, etc
        // and then return the version when installation is done
        // most of the time it will NOT be the bundle version, it's the version when THIS script is done
        // an update will be performed after the installation to update to the bundle version
        return new Version('1.0.0');
    }
}
```

Create an update script
=======================

Declare a service with tag bundle.update :
```yml
# MyBundle/Resources/config/services.yml
services :
    mybundle.updater:
        class: MyBundle\Installer\Update
        tags:
            - { name: bundle.update }
```

Create the service who implements Update :
```php
# MyBundle/Installer/Update.php
namespace MyBundle/Installer;

use kujaff\VersionsBundle\Installer\Update as BaseUpdate;
use kujaff\VersionsBundle\Versions\Version;
use kujaff\VersionsBundle\Versions\BundleVersion;

class Update implements BaseUpdate
{
    public function getBundleName()
    {
        return 'MyBundle';
    }

    public function update(BundleVersion $bundleVersion)
    {
        // make stuff to update your bundle, like creating dirs, updating database schema, etc
        // and then return the version when update is done
        // to get the installed version, see $bundleVersion->getInstalledVersion()
        return new Version('1.0.3');
    }
}
```

Create an uninstall script
==========================

Declare a service with tag bundle.uninstall :

    # MyBundle/Resources/config/services.yml
    services :
        mybundle.installer:
            class: MyBundle\Installer\Uninstall
            tags:
                - { name: bundle.uninstall }

Create the service who implements Uninstall :

	# MyBundle/Installer/Uninstall.php
	namespace MyBundle/Installer;

	use kujaff\VersionsBundle\Installer\Uninstall as BaseUninstall;
	use kujaff\VersionsBundle\Versions\Version;

	class Uninstall implements BaseUninstall
	{
		public function getBundleName()
		{
			return 'MyBundle';
		}

		public function uninstall()
		{
			// make stuff to uninstall your bundle, like removing dirs, removing database tables, etc
		}
	}

Extends EasyInstaller to make your installer easiest
====================================================

An easiest way to create install / updates / uninstall for your bundle is to create a service who extends EasyInstaller.
You just need to add @service_container in parameters for your service :
	
	# MyBundle/Resources/config/services.yml
	services :
		mybundle.installer:
		class: MyBundle\Installer\Installer
		arguments: [ @service_container ]
		tags:
			- { name: bundle.install }
			- { name: bundle.update }
			- { name: bundle.uninstall }


Available methods :

	_executeDQL($sql, $parameters = array())
		Execute a DQL query, with parameters
		
	_executeSQL($sql, $parameters = array())
		Execute a SQL query, with parameters
	
	_updateOneVersionOneMethod(Update $updater, BundleVersion $bundleVersion)
		Call it from your update method, like $this->_updateOneVersionOneMethod($this, $bundleVersion);
		Each methods prefixed by 'update_' will be parsed to see if we need to call it for the current update.
		If a version doesn't need a patch, don't create an empty method, it's useless.
		Example :
			-> Current bundle installed version : 1.0.0
			-> Current bundle files version : 1.0.3
			1) Try to find a method named update_1_0_1 to update bundle from 1.0.0 to 1.0.1
			2) Try to find a method named update_1_0_2 to update bundle from 1.0.1 to 1.0.2
			3) Try to find a method named update_1_0_3 to update bundle from 1.0.2 to 1.0.3
			
	_dropTables(array $tables)
		Drop tables only if exists (DROP TABLE IF EXISTS)

Install a bundle
================

You can use SF2 console :
	
	php app/console bundle:install MyBundle
	php app/console bundle:install:all

Or you can use Installer service :
	
	$container->get('bundle.installer')->install('MyBundle');

Update a bundle
===============

You can use SF2 console :
	
	php app/console bundle:upate MyBundle
	php app/console bundle:upate:all

Or you can use Installer service :
	
	$container->get('bundle.installer')->update('MyBundle');

Uninstall a bundle
==================

You can use SF2 console :
	
	php app/console bundle:uninstall MyBundle
	php app/console bundle:uninstall MyBundle --force

Or you can use Installer service :
	
	$container->get('bundle.installer')->uninstall('MyBundle');

Get bundle version informations
===============================

You can use SF2 console :

	php app/console bundle:version MyBundle

Or you can use Version service :

	# will return an instance of kujaff\VersionsBundle\Versions\VersionnedBundle
	# getVersion() is bundle files version
	# getInstalledVersion() is the installed version
	# see VersionnedBundle for other methods
	$container->get('bundle.version')->getBundleVersion('MyBundle');

Get all versionned bundles informations
=======================================

You can use SF2 console :

	php app/console bundle:list
