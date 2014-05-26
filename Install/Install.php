<?php

namespace kujaff\VersionsBundle\Install;

use kujaff\VersionsBundle\Installer\Install as BaseInstall;
use kujaff\VersionsBundle\Versions\Version;
use kujaff\VersionsBundle\Installer\DoctrineHelper;
use kujaff\VersionsBundle\Installer\BundleNameFromClassName;
use kujaff\VersionsBundle\Installer\ContainerAware;

class Installer implements BaseInstall
{

	use DoctrineHelper,
	 BundleNameFromClassName,
	 ContainerAware;

	public function install()
	{
		$this->_dropTables(array('versions_bundles'));
		$this->_executeSQL('
			CREATE TABLE `versions_bundles` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
				`installationDate` datetime NOT NULL,
				`installedVersion` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
				`updateDate` datetime DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		');

		return new Version('1.0.0');
	}

}