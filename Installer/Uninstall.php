<?php
namespace kujaff\VersionsBundle\Installer;

/**
 * Interface to implements for uninstalling a bundle
 */
interface Uninstall
{

	public function getBundleName();

	/**
	 * Uninstall
	 */
	public function uninstall();
}