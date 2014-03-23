<?php
namespace kujaff\VersionsBundle\Installer;

use kujaff\VersionsBundle\Entity\BundleVersion;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Methods to help you making and installer / updater / uninstaller
 */
abstract class EasyInstaller
{
	/**
	 * Update methods
	 *
	 * @var array
	 */
	private $patchs = array();

	/**
	 * @var ContainerInterface
	 */
	protected $container;

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
	 * Execute a DQL query (only for SELECT, UPDATE or DELETE)
	 *
	 * @param string $dql
	 * @param array $parameters
	 * @return mixed
	 */
	protected function _executeDQL($dql, $parameters = array())
	{
		$em = $this->container->get('doctrine')->getManager();
		$query = $em->createQuery($dql);
		foreach ($parameters as $name => $value) {
			$query->setParameter($name, $value);
		}
		return $query->getResult();
	}

	/**
	 * Execute raw SQL
	 *
	 * @param string $sql
	 * @param array $parameters
	 */
	protected function _executeSQL($sql, $parameters = array())
	{
		$em = $this->container->get('doctrine')->getManager();
		$stmt = $em->getConnection()->prepare($sql);
		foreach ($parameters as $name => $value) {
			$stmt->bindValue($name, $value);
		}
		return $stmt->execute();
	}

	/**
	 * Find update methods (syntax : update_X_Y_Z)
	 * Call it from constructor of your Updater service, with $this as $object parameter
	 *
	 * @param UpdateMethods $object
	 */
	protected function _findUpdateMethods(UpdateMethods $object)
	{
		$return = array();
		foreach (get_class_methods(get_class($object)) as $method) {
			if (substr($method, 0, 7) == 'update_') {
				$version = substr($method, 7);
				if (preg_match('/[0-9]{1,}[_]{1}[0-9]{1,}[_]{1}[0-9]{1,}/', $version)) {
					$this->patchs[] = str_replace('_', '.', $version);
				}
			}
		}
		sort($return);
		return $return;
	}

	/**
	 * Update bundle
	 *
	 * @param Update $updater Use $this in your class
	 * @param BundleVersion $bundleVersion
	 */
	protected function _updateOneVersionOneMethod(Update $updater, BundleVersion $bundleVersion)
	{
		$methods = $this->_findUpdateMethods($updater);
		$service = $this->container->get('bundle.version');
		foreach ($methods as $method) {
			if ($service->compare($method, $bundleVersion->getInstalledVersion()) == 1 && $service->compare($method, $bundleVersion->getVersion()) <= 0) {
				$this->{'update_' . str_replace('.', '_', $method)}();
			}
		}
		return $bundleVersion->getVersion();
	}

}