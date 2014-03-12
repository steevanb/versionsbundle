versionsbundle
==============

Add version information to your bundles, with updates and mores

Installation
============

Composer :

    # composer.json
    {
        "require": {
            "kujaff/versionsbundle": "dev-master"
        }
    }

Add bundle to your AppKernel :

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

Add version type in your Doctrine config :

    # app/config/config.yml
    doctrine:
        dbal:
            types:
                version: kujaff\VersionsBundle\Versions\DoctrineType

Make your bundle versionned
===========================

Makes your bundle versionned by extending VersionnedBundle instead of Bundle :

    # MyBundle/MyBundle.php
    use kujaff\VersionsBundle\Versions\VersionnedBundle;
    use kujaff\VersionsBundle\Versions\Version;

    class DashboardBundle extends VersionnedBundle
    {
        public function __construct()
        {
            $this->version = new Version('1.0.0');
        }
    }

Make an install script
======================

Create a service with tag 'bundle.install', who implements Install :

    # MyBundle/Resources/config/services.yml
    services :
        mybundle.installer:
            class: MyBundle\Installer\Install
            tags:
                - { name: bundle.install }

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

Create an update script by implementing Update
==============================================

Create a service with tag 'bundle.install', who implements Update :

    # MyBundle/Resources/config/services.yml
    services :
        mybundle.updater:
            class: MyBundle\Installer\Update
            tags:
                - { name: bundle.update }

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
            return new Version('1.0.1');
	}
    }

Create an update script by extending UpdateMethods
==================================================

An easiest way to create updates for your bundle is to create a service with tag 'bundle.install', who extends UpdateMethods.
Each methods prefixed by 'update_' will be parsed to see if we need to call it for the current update.
If a version doesn't need a patch, don't create an empty method, it's useless
Example :
  -> Current bundle installed version : 1.0.0
  -> Current bundle files version : 1.0.3
  1) Try to find a method named update_1_0_1 to update bundle from 1.0.0 to 1.0.1
  2) Try to find a method named update_1_0_2 to update bundle from 1.0.1 to 1.0.2
  3) Try to find a method named update_1_0_3 to update bundle from 1.0.2 to 1.0.3

    # MyBundle/Resources/config/services.yml
    services :
        mybundle.updater:
            class: MyBundle\Installer\Update
            arguments: [ @service_container ]
            tags:
                - { name: bundle.update }

    # MyBundle/Installer/Update.php
    namespace MyBundle/Installer;

    use kujaff\VersionsBundle\Installer\UpdateMethods;
    use kujaff\VersionsBundle\Versions\Version;
    use kujaff\VersionsBundle\Versions\BundleVersion;

    class Update extends UpdateMethods
    {
        public function __construct(ContainerInterface $container)
	{
            parent::__construct($container);
            // call findUpdateMethods to parse your class and find methods prefixed by update_
            $this->_findUpdateMethods($this);
	}

	public function getBundleName()
	{
            return 'MyBundle';
	}

	public function update_1_0_1()
	{
            // called when version is inferior to 1.0.1, to update it to 1.0.1
	}

        public function update_1_0_3()
	{
            // called when version is inferior to 1.0.3, to update it to 1.0.3
	}
    }
