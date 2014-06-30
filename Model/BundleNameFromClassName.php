<?php
namespace kujaff\VersionsBundle\Model;

/**
 * Define a protected property ContainerInterface $container and define it in __construct(ContainerInterface $container)
 */
trait BundleNameFromClassName
{

	/**
	 * Return bundle name, search for it in class namespace
	 *
	 * @return string
	 */
	public function getBundleName()
	{
		$parts = array_reverse(explode('\\', get_called_class()));
		foreach ($parts as $part) {
			if (substr($part, -6) == 'Bundle') {
				return $part;
			}
		}
		throw \Exception('Bundle name cannot be found in "' . get_called_class() . '" class name.');
	}

}