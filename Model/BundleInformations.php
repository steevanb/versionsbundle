<?php

namespace kujaff\VersionsBundle\Model;

use kujaff\VersionsBundle\Exception\BundleNotFoundException;

trait BundleInformations
{

	/**
	 * Return bundle informations
	 *
	 * @param string $name
	 * @return \Symfony\Component\HttpKernel\Bundle\Bundle
     * @throws BundleNotFoundException
	 */
	protected function getBundleInformations($name)
	{
		$bundles = $this->container->get('kernel')->getBundles();
		if (array_key_exists($name, $bundles) === false) {
			throw new BundleNotFoundException('Bundle "' . $name . '" not found.');
		}
		return $bundles[$name];
	}

}
