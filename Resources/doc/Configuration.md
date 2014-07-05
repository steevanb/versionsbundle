Install and update orders
=========================

You can specify install order, and update order for your versionned bundles.

It will be used by some commands, like bundle:install:all and bundle:update:all.

VersionsBundle must be first, with force: true parameter, cause without it, others bundles can't be installed and updated.
```yml
# app/config/config.yml
versions:
    installOrder:
        - VersionsBundle: { force: true }
        - MyBundle: ~
        - MyOtherBundle: { force: true }
    updateOrder:
        - { bundle: MyBundle, version: 1.1.0 }
        - { bundle: MyOtherBundle, version: 2.0.0 }
        - { bundle: MyBundle, version: 1.3.0 }
```

Cancel installed and updated bundles checks
===========================================

You can cancel installed and updated bundles verifications, although bundles are configured to do it.

It must be configured for production environment, for example.
```yml
# app/config/config.yml
versions:
    checkNeedInstallation: [TRUE|false]
    checkNeedUpToDate: [TRUE|false]
```