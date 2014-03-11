<?php
namespace kujaff\VersionsBundle\Versions;

use Symfony\Component\DependencyInjection\ContainerInterface;
use kujaff\VersionsBundle\Entity\Bundle;
use kujaff\VersionsBundle\Install\Install;
use kujaff\VersionsBundle\Install\PostSchema;

class Service
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * Return tagged services
	 *
	 * @param string $tag
	 * @return array
	 * @throws \Exception
	 */
	private function _getServices($tag)
	{
		$fileName = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'services.bundle.' . $tag . '.php';
		if (file_exists($fileName) === false) {
			throw new \Exception('Unable to find service tagged "bundle."' . $tag . '.');
		}
		return require($fileName);
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
	 * Get version information about a bundle
	 *
	 * @param string $bundle
	 * @return Bundle
	 */
	public function get($name)
	{
		$doctrine = $this->container->get('doctrine');
		$return = $doctrine->getRepository('VersionsBundle:Bundle')->findOneByName($name);
		if ($return == null) {
			$return = new Bundle($name);
			$doctrine->getEntityManager()->persist($return);
		}
		$bundle = _service('kernel')->getBundle('DashboardBundle');
		if ($bundle instanceof VersionnedBundle) {
			$return->setVersion($bundle->getVersion());
		}
		return $return;
	}

	/**
	 * Installation before schema update
	 *
	 * @param string $name
	 * @throws \Exception
	 */
	public function installPreSchema($name)
	{
		$manager = $this->container->get('doctrine')->getEntityManager();
		foreach ($this->_getServices('install') as $id) {
			$service = $this->container->get($id);
			if (!$service instanceof Install) {
				throw new \Exception('Service "' . $service . '" must implements kujaff\VersionsBundle\Install\Install.');
			}

			if ($service->getBundleName() == $name) {
				$bundleVersion = $this->get($service->getBundleName());
				if ($bundleVersion->getVersion() == null) {
					throw new \Exception('Bundle "' . $service->getBundleName() . '" must have a version defined in his main class, via getVersion method.');
				}

				if ($bundleVersion->getInstallationPreSchema()) {
					throw new \Exception('Pre schema update installation of "' . $service->getBundleName() . '" is already done.');
				} else if ($service instanceof PreSchema) {
					$version = $service->preSchema();
					$bundleVersion->setInstalledVersion($version);
				}

				$bundleVersion->setInstallationDate(new \DateTime());
				$bundleVersion->setInstallationPreSchema(true);
				$manager->flush();
			}
		}
	}

	/**
	 * Installation after schema update
	 *
	 * @param string $name
	 * @throws \Exception
	 */
	public function installPostSchema($name)
	{
		$manager = $this->container->get('doctrine')->getEntityManager();
		foreach ($this->_getServices('install') as $id) {
			$service = $this->container->get($id);
			if (!$service instanceof Install) {
				throw new \Exception('Service "' . $service . '" must implements kujaff\VersionsBundle\Install\Install.');
			}

			if ($service->getBundleName() == $name) {
				$bundleVersion = $this->get($service->getBundleName());
				if ($bundleVersion->getVersion() == null) {
					throw new \Exception('Bundle "' . $service->getBundleName() . '" must have a version defined in his main class, via getVersion method.');
				}

				if ($bundleVersion->getInstallationPostSchema()) {
					throw new \Exception('Post schema update installation of "' . $service->getBundleName() . '" is already done.');
				} else if ($service instanceof PostSchema) {
					$version = $service->postSchema();
					$bundleVersion->setInstalledVersion($version);
				}

				$bundleVersion->setInstallationDate(new \DateTime());
				$bundleVersion->setInstallationPostSchema(true);
				$manager->flush();
			}
		}
	}

	/**
	 * Uninstall a bundle
	 *
	 * @param string $name
	 * @throws \Exception
	 */
	public function uninstall($name)
	{
		$version = $this->get($name);
		$manager = $this->container->get('doctrine')->getEntityManager();

		if ($version->isInstalled() == false) {
			throw new \Exception('Bundle "' . $name . '" is not installed.');
		}

		foreach ($this->_getServices('uninstall') as $serviceId) {
			$service = $this->container->get($serviceId);
			if ($service->getBundleName() == $name) {
				if (!$service instanceof Uninstall) {
					throw new \Exception('Service "' . $serviceId . '" must implements kujaff\VersionsBundle\Uninstall\Uninstall.');
				}
				$service->uninstall();
			}
		}

		$manager->remove($version);
		$manager->flush();
	}

}