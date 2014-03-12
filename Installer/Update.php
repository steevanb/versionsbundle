<?php
namespace kujaff\VersionsBundle\Installer;

use kujaff\VersionsBundle\Entity\BundleVersion;

/**
 * Update a bundle
 */
interface Update
{

	/**
	 * Get bundle name
	 */
	public function getBundleName();

	/**
	 * Update bundle
	 *
	 * @param BundleVersion $bundleVersion
	 */
	public function update(BundleVersion $bundleVersion);
}