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

use kujaff\VersionsBundle\Model\UpdateInterface;
use kujaff\VersionsBundle\Entity\Version;
use kujaff\VersionsBundle\Entity\BundleVersion;

class Update implements UpdateInterface
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