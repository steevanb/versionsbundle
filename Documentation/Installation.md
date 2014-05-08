Composer :
```json
// composer.json
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
