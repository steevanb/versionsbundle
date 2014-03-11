<?php
namespace kujaff\VersionsBundle\Versions;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Extends this class instead of Bundle SF2 class to add getVersion
 */
class VersionnedBundle extends Bundle
{
	/**
	 * Version
	 *
	 * @var Version
	 */
	protected $version;

	/**
	 * Return version
	 *
	 * @return Version
	 */
	public function getVersion()
	{
		return $this->version;
	}

}