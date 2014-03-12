<?php
namespace kujaff\VersionsBundle\Installer;

/**
 * Interface to implements to install bundle
 */
interface Install
{

	/**
	 * Get bundle name
	 */
	public function getBundleName();

	/**
	 * Install bundle
	 */
	public function install();
}