<?php

namespace kujaff\VersionsBundle\Installer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use kujaff\VersionsBundle\Versions\Version;
use kujaff\VersionsBundle\Entity\BundleVersion;
use kujaff\VersionsBundle\Exception\StructureException;
use kujaff\VersionsBundle\Exception\InstallStateException;

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
	 * @param $implements Fully qualified interface name must be implemented
	 * @return array
	 */
	private function _getService($bundle, $tag, $implements)
	{
		$fileName = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'services.bundle.' . $tag . '.php';
		if (file_exists($fileName) === false) {
			throw new StructureException('Unable to find service tagged "bundle."' . $tag . '.');
		}

		$serviceIds = require($fileName);
		foreach ($serviceIds as $id) {
			$service = $this->container->get($id);
			if (!$service instanceof $implements) {
				throw new StructureException('Service "' . $id . '" must implements ' . $implements . '.');
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
			throw new StructureException('Bundle "' . $bundle . '" main class must extends kujaff\VersionsBundle\Versions\VersionnedBundle.');
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
	 * @param boolean $force Force installation
	 * @throws \Exception
	 */
	public function install($bundle, $force = false)
	{
		$manager = $this->container->get('doctrine')->getManager();
		if ($force == false) {
			$bundleVersion = $this->_getBundleVersion($bundle);

			// already installed
			if ($bundleVersion->isInstalled()) {
				throw new InstallStateException('Bundle "' . $bundle . '" is already installed.');
			}
		}

		$service = $this->_getService($bundle, 'install', 'kujaff\\VersionsBundle\\Installer\\Install');
		// bundle has a service to do stuff
		if ($service !== false) {
			$installedVersion = $service->install();
			if (!$installedVersion instanceof Version) {
				throw new StructureException('Service "' . get_class($service) . '" install method must return an instance of kujaff\VersionsBundle\Versions\Version.');
			}

			if ($force == true) {
				$bundleVersion = $this->_getBundleVersion($bundle);
			}
			$bundleVersion->setInstalledVersion($installedVersion);
			$bundleVersion->setInstallationDate(new \DateTime());
			$manager->persist($bundleVersion);
			$manager->flush();
			return $installedVersion;
		}

		// no install service for this bundle, assume we installed the latest version
		if ($force == true) {
			$bundleVersion = $this->_getBundleVersion($bundle);
		}
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
			throw new InstallStateException('Bundle "' . $bundle . '" is not installed.');
		}

		// already up to date
		if ($bundleVersion->getInstalledVersion()->asString() == $bundleVersion->getVersion()->asString()) {
			return $bundleVersion->getInstalledVersion();
		}

		$service = $this->_getService($bundle, 'update', 'kujaff\\VersionsBundle\\Installer\\Update');
		// an update service has be found
		if ($service !== false) {
			$installedVersion = $service->update($bundleVersion);

			// no update service, assume we have updated bundle to the latest version
		} else {
			$installedVersion = $bundleVersion->getVersion();
		}

		$bundleVersion->setInstalledVersion($installedVersion);
		$bundleVersion->setUpdateDate(new \DateTime());
		//$this->container->get('doctrine')->getManager()->flush();

		return $installedVersion;
	}

	/**
	 * Uninstall
	 *
	 * @param string $bundle
	 * @param boolean $force Force uninstall, although it's not installed
	 * @throws \Exception
	 */
	public function uninstall($bundle, $force = false)
	{
		$manager = $this->container->get('doctrine')->getManager();
		$bundleVersion = $this->_getBundleVersion($bundle);
		if ($force == false && $bundleVersion->isInstalled() == false) {
			throw new InstallStateException('Bundle "' . $bundle . '" is not installed.');
		}

		$service = $this->_getService($bundle, 'uninstall', 'kujaff\\VersionsBundle\\Installer\\Uninstall');
		if ($service !== false) {
			$service->uninstall();
		}

		$manager->remove($bundleVersion);
		$manager->flush();
	}

}