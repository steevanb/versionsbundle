<?php
namespace kujaff\VersionsBundle\Installer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use kujaff\VersionsBundle\Versions\Version;
use kujaff\VersionsBundle\Entity\BundleVersion;

class Service
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Return tagged services
	 *
	 * @param string $bundle
	 * @param string $tag
	 * @return array
	 * @throws \Exception
	 */
	private function _getService($bundle, $tag)
	{
		$fileName = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'services.bundle.' . $tag . '.php';
		if (file_exists($fileName) === false) {
			throw new \Exception('Unable to find service tagged "bundle."' . $tag . '.');
		}
		$serviceIds = require($fileName);
		foreach ($serviceIds as $id) {
			$service = $this->container->get($id);
			if (!$service instanceof Install) {
				throw new \Exception('Service "' . $id . '" must implements kujaff\VersionsBundle\Installer\Install.');
			}
			if ($service->getBundleName() == $bundle) {
				return $service;
			}
		}
		return false;
	}

	/**
	 * Get bundle version, assert a version si defined
	 *
	 * @param string $bundle
	 * @return BundleVersion
	 * @throws \Exception
	 */
	private function _getBundleVersion($bundle)
	{
		$return = $this->container->get('bundle.version')->getBundleVersion($bundle);
		if ($return->getVersion() == null) {
			throw new \Exception('Bundle "' . $bundle . '" main class must extends kujaff\VersionsBundle\Versions\VersionnedBundle.');
		}
		return $return;
	}

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Install
	 *
	 * @param string $bundle
	 * @throws \Exception
	 */
	public function install($bundle)
	{
		$manager = $this->container->get('doctrine')->getEntityManager();
		$bundleVersion = $this->_getBundleVersion($bundle);

		// already installed
		if ($bundleVersion->isInstalled()) {
			throw new \Exception('Bundle "' . $bundle . '" is already installed.');
		}

		$service = $this->_getService($bundle, 'install');
		// bundle has a service to do stuff
		if ($service !== false) {
			if (!$service instanceof Install) {
				throw new \Exception('Service "' . get_class($service) . '" must implements kujaff\VersionsBundle\Installer\Install.');
			}

			$installedVersion = $service->install();
			if (!$installedVersion instanceof Version) {
				throw new \Exception('Service "' . get_class($service) . '" install method must return an instance of kujaff\VersionsBundle\Versions\Version.');
			}
			$bundleVersion->setInstalledVersion($installedVersion);
			$bundleVersion->setInstallationDate(new \DateTime());
			$manager->persist($bundleVersion);
			$manager->flush();
			return $installedVersion;
		}

		// no install service for this bundle, assume we installed the latest version
		$bundleVersion->setInstalledVersion($bundleVersion->getVersion());
		$bundleVersion->setInstallationDate(new \DateTime());
		$manager->persist($bundleVersion);
		$manager->flush();
		return $bundleVersion->getInstalledVersion();
	}

	/**
	 * Update
	 *
	 * @param string $bundle
	 */
	public function update($bundle)
	{
		$bundleVersion = $this->_getBundleVersion($bundle);
		if ($bundleVersion->isInstalled() == false) {
			throw new \Exception('Bundle "' . $bundle . '" is not installed.');
		}

		// already up to date
		if ($bundleVersion->getInstalledVersion()->asString() == $bundleVersion->getVersion()->asString()) {
			return $bundleVersion->getInstalledVersion();
		}

		$service = $this->_getService($bundle, 'update');
		// an update service has be found
		if ($service !== false) {
			if (!$service instanceof Update) {
				throw new \Exception('Service "' . get_class($service) . '" must implements kujaff\VersionsBundle\Installer\Update.');
			}
			$installedVersion = $service->update($bundleVersion);

			// no update service, assume we have updated bundle to the latest version
		} else {
			$installedVersion = $bundleVersion->getVersion();
		}

		$bundleVersion->setInstalledVersion($installedVersion);
		$bundleVersion->setUpdateDate(new \DateTime());
		$this->container->get('doctrine')->getEntityManager()->flush();

		return $installedVersion;
	}

	/**
	 * Uninstall
	 *
	 * @param string $bundle
	 * @throws \Exception
	 */
	public function uninstall($bundle)
	{
		$manager = $this->container->get('doctrine')->getEntityManager();
		$bundleVersion = $this->_getBundleVersion($bundle);
		if ($bundleVersion->isInstalled() == false) {
			throw new \Exception('Bundle "' . $bundle . '" is not installed.');
		}

		$service = $this->_getService($bundle, 'uninstall');
		if ($service !== false) {
			if (!$service instanceof Uninstall) {
				throw new \Exception('Service "' . get_class($service) . '" must implements kujaff\VersionsBundle\Installer\Uninstall.');
			}
			$service->uninstall();
		}

		$manager->remove($bundleVersion);
		$manager->flush();
	}

}