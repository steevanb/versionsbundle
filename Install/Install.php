<?php
namespace kujaff\VersionsBundle\Install;

/**
 * Interface to implements to install bundle
 */
interface Install
{

	/**
	 * Get bundle name
	 */
	public function getBundleName();
}