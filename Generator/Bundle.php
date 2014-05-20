<?php
namespace kujaff\VersionsBundle\Generator;

use kujaff\VersionsBundle\Installer\ContainerAware;

/**
 * Generate files to make bundle versionned
 */
class Bundle
{

	use ContainerAware,
	 BundleInformations;

	/**
	 * Generate verything to make your bundle versionned
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 * @param string $version Version after installation, ex '1.0.0'
	 */
	public function generate($bundle, $version)
	{
		$this->generateInstallService($bundle, $version);
		$this->generateUninstallService($bundle);
	}

	/**
	 * Generate service and register it for installation
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 * @param string $version Version after installation, ex '1.0.0'
	 */
	public function generateInstallService($bundle, $version)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);

		// do not create service if another one is already registered
		$registerService = $this->container->get('bundle.generator.registerService');
		if ($registerService->existsTagged($bundle, 'bundle.install')) {
			return false;
		}

		// generate service PHP code
		$templating = $this->container->get('templating');
		$params = array(
			'bundle' => $bundleInfos->getName(),
			'namespace' => $bundleInfos->getNamespace() . '\\Install',
			'uses' => array(
				'kujaff\\VersionsBundle\\Versions\\Version',
				'kujaff\\VersionsBundle\\Installer\\Install as BaseInstall',
				'kujaff\\VersionsBundle\\Installer\\ContainerAware',
				'kujaff\\VersionsBundle\\Installer\\BundleNameFromClassName',
				'kujaff\\VersionsBundle\\Installer\\DoctrineHelper'
			),
			'className' => 'Install',
			'implements' => array('BaseInstall'),
			'traits' => array('ContainerAware', 'BundleNameFromClassName', 'DoctrineHelper'),
			'version' => $version
		);
		$templating->render('VersionsBundle:skeleton:install.php.twig', $params);

		$serviceOptions = array(
			'arguments' => array('@service_container'),
			'tags' => array(array('name' => 'bundle.install'))
		);
		$registerService->register($bundle, strtolower($bundle) . '.install', $bundleInfos->getNamespace() . '\\Install\\Install', $serviceOptions);

		return true;
	}

	/**
	 * Generate service and register it for uninstall
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 */
	public function generateUninstallService($bundle)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);

		// do not create service if another one is already registered
		$registerService = $this->container->get('bundle.generator.registerService');
		if ($registerService->existsTagged($bundle, 'bundle.uninstall')) {
			return false;
		}

		// generate service PHP code
		$templating = $this->container->get('templating');
		$params = array(
			'bundle' => $bundleInfos->getName(),
			'namespace' => $bundleInfos->getNamespace() . '\\Install',
			'uses' => array(
				'kujaff\\VersionsBundle\\Installer\\Uninstall as BaseUninstall',
				'kujaff\\VersionsBundle\\Installer\\ContainerAware',
				'kujaff\\VersionsBundle\\Installer\\BundleNameFromClassName',
				'kujaff\\VersionsBundle\\Installer\\DoctrineHelper'
			),
			'className' => 'Uninstall',
			'implements' => array('BaseUninstall'),
			'traits' => array('ContainerAware', 'BundleNameFromClassName', 'DoctrineHelper')
		);
		$templating->render('VersionsBundle:skeleton:uninstall.php.twig', $params);

		// register service
		$serviceOptions = array(
			'arguments' => array('@service_container'),
			'tags' => array(array('name' => 'bundle.uninstall'))
		);
		$registerService->register($bundle, strtolower($bundle) . '.uninstall', $bundleInfos->getNamespace() . '\\Install\\Uninstall', $serviceOptions);

		return true;
	}

}