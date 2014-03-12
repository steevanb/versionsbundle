<?php
namespace kujaff\VersionsBundle\Installer;

use kujaff\VersionsBundle\Entity\BundleVersion;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Update a bundle
 */
abstract class UpdateMethods implements Update
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
	 * Find update methods (syntax : update_X_Y_Z)
	 * Call it from constructor of your Updater service, with $this as $object parameter
	 *
	 * @param UpdateMethods $object
	 */
	protected function _findUpdateMethods(UpdateMethods $object)
	{
		$this->patchs = array();
		foreach (get_class_methods(get_class($object)) as $method) {
			if (substr($method, 0, 7) == 'update_') {
				$version = substr($method, 7);
				if (preg_match('/[0-9]{1,}[_]{1}[0-9]{1,}[_]{1}[0-9]{1,}/', $version)) {
					$this->patchs[] = str_replace('_', '.', $version);
				}
			}
		}
		sort($this->patchs);
	}

	/**
	 * Update bundle
	 *
	 * @param BundleVersion $bundleVersion
	 */
	public function update(BundleVersion $bundleVersion)
	{
		$service = $this->container->get('bundle.version');
		foreach ($this->patchs as $patch) {
			if ($service->compare($patch, $bundleVersion->getInstalledVersion()) == 1 && $service->compare($patch, $bundleVersion->getVersion()) <= 0) {
				$this->{'update_' . str_replace('.', '_', $patch)}();
			}
		}
		return $bundleVersion->getVersion();
	}

}