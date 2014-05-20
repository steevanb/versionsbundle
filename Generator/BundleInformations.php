<?php
namespace kujaff\VersionsBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

trait BundleInformations
{

	/**
	 * Return bundle informations
	 *
	 * @param string $bundleName
	 * @return BaseBundle
	 */
	protected function _getBundleInformations($bundleName)
	{
		$bundles = $this->container->getParameter('kernel.bundles');
		if (array_key_exists($bundleName, $bundles) == false) {
			throw new \Exception('Bundle "' . $bundleName . '" doesn\'t exists or is not installed (most of the time in app/AppKernel.php::registerBundles).');
		}
		return new $bundles[$bundleName]();
	}

}