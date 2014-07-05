Install and update orders
=========================

You can specify install order, and update order for your versionned bundles.
```yml
# app/config/config.yml
versions:
    installOrder:
        - MyBundle: ~
        - MyOtherBundle: { force: true }
    updateOrder:
        - { bundle: MyBundle, version: 1.1.0 }
        - { bundle: MyOtherBundle, version: 2.0.0 }
        - { bundle: MyBundle, version: 1.3.0 }
```

Cancel installed and updated checks
===================================

You can cancel installed and updated bundles verifications, although bundles are configured to do it.

It must be configured for production environment, for example.
```yml
# app/config/config.yml
versions:
    checkNeedInstallation: [TRUE|false]
    checkNeedUpToDate: [TRUE|false]
```