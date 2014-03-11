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