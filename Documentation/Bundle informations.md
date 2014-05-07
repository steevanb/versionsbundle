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
