2.2.0
=====

- [Moved] Trait BundleInformations moved from Service/Generator to Model/

- [Add] .gitignore

- [Dependency] Add steevanb/codegenerator 1.*


2.1.0
=====

- [Add] Configuration versions.checkNeedInstallation, to indicate if VersionsBundle has to check if versionned bundles needs to be installed. Must be false on prod environment.

- [Add] Configuration versions.checkNeedUpToDate, to indicate if VersionsBundle has to check if versionned bundles needs to be updated. Must be false on prod environment.

- [Add] Settters and getters for needInstallation and needUpToDate on VersionnedBundle
