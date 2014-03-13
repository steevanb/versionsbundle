<?php
namespace kujaff\VersionsBundle\Versions;

use Symfony\Component\DependencyInjection\ContainerInterface;
use kujaff\VersionsBundle\Entity\BundleVersion;

/**
 * Service for BundleVersions
 */
class Service
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

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
	 * Get bundle version informations
	 *
	 * @param string $bundle
	 * @return BundleVersion
	 */
	public function getBundleVersion($bundle)
	{
		$doctrine = $this->container->get('doctrine');
		$return = $doctrine->getRepository('VersionsBundle:BundleVersion')->findOneByName($bundle);
		if ($return == null) {
			$return = new BundleVersion($bundle);
		}
		$bundle = $this->container->get('kernel')->getBundle($bundle);
		if ($bundle instanceof VersionnedBundle) {
			$return->setVersion($bundle->getVersion());
		}
		return $return;
	}

	/**
	 * Return -1 if $version1 < $version2, 0 if $version1 = $version2, +1 if $version1 > $version2
	 *
	 * @param mixed $version1 Can be a string (X.Y.Z) or a Version instance
	 * @param mixed $version2 Can be a string (X.Y.Z) or a Version instance
	 */
	public function compare($version1, $version2)
	{
		if (is_string($version1)) {
			$version1 = new Version($version1);
		}
		if (is_string($version2)) {
			$version2 = new Version($version2);
		}

		$lengthMajor = max(strlen($version1->getMajor()), strlen($version2->getMajor()));
		$lengthMinor = max(strlen($version1->getMinor()), strlen($version2->getMinor()));
		$lengthPatch = max(strlen($version1->getPatch()), strlen($version2->getPatch()));
		$version1Number = sprintf('%0' . $lengthMajor . 's', $version1->getMajor()) . sprintf('%0' . $lengthMinor . 's', $version1->getMinor()) . sprintf('%0' . $lengthPatch . 's', $version1->getPatch());
		$version2Number = sprintf('%0' . $lengthMajor . 's', $version2->getMajor()) . sprintf('%0' . $lengthMinor . 's', $version2->getMinor()) . sprintf('%0' . $lengthPatch . 's', $version2->getPatch());

		if ($version1Number == $version2Number) {
			return 0;
		} else {
			return ($version1Number > $version2Number) ? 1 : -1;
		}
	}

}