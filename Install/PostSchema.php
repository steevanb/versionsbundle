<?php
namespace kujaff\VersionsBundle\Install;

use kujaff\VersionsBundle\Versions\Version;

/**
 * Interface to implements to add post schema update method
 */
interface PostSchema
{

	/**
	 * Called after schema update
	 *
	 * @return Version
	 */
	public function postSchema();
}