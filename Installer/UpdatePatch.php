<?php

namespace kujaff\VersionsBundle\Installer;

use kujaff\VersionsBundle\Entity\BundleVersion;
use kujaff\VersionsBundle\Versions\Version;

/**
 * Interface to implements when your Update service use UpdateByPatchs trait
 */
interface UpdatePatch
{

	/**
	 * Do the update
	 *
	 * @param BundleVersion $version
	 * @return Version
	 */
	public function update(BundleVersion $version);
}