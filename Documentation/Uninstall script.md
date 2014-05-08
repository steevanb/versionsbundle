Declare a service with tag bundle.uninstall :

```yml
# MyBundle/Resources/config/services.yml
services :
    mybundle.installer:
        class: MyBundle\Installer\Uninstall
        tags:
            - { name: bundle.uninstall }
```

Create the service who implements Uninstall :

```php
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
```