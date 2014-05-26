<?php

namespace kujaff\VersionsBundle\Install;

use kujaff\VersionsBundle\Installer\Uninstall as BaseUninstall;
use kujaff\VersionsBundle\Installer\DoctrineHelper;
use kujaff\VersionsBundle\Installer\BundleNameFromClassName;
use kujaff\VersionsBundle\Installer\ContainerAware;

class Uninstall implements BaseUninstall
{

	use DoctrineHelper,
	 BundleNameFromClassName,
	 ContainerAware;

	public function uninstall()
	{
		$this->_dropTables(array('versions_bundles'));
	}

}