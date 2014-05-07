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