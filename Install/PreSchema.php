<?php
namespace kujaff\VersionsBundle\Install;

use kujaff\VersionsBundle\Versions\Version;

/**
 * Interface to implements to add pre schema update method
 */
interface PreSchema
{

	/**
	 * Called after schema update
	 *
	 * @return Version
	 */
	public function preSchema();
}