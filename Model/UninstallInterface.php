<?php
namespace kujaff\VersionsBundle\Model;

/**
 * Interface to implements for uninstalling a bundle
 */
interface UninstallInterface
{

	public function getBundleName();

	/**
	 * Uninstall
	 */
	public function uninstall();
}
