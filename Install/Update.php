<?php

namespace kujaff\VersionsBundle\Install;

use kujaff\VersionsBundle\Installer\Update as BaseUpdate;
use kujaff\VersionsBundle\Installer\DoctrineHelper;
use kujaff\VersionsBundle\Installer\BundleNameFromClassName;
use kujaff\VersionsBundle\Installer\ContainerAware;
use kujaff\VersionsBundle\Installer\UpdateOneVersionOneMethod;

class Update implements BaseUpdate
{

	use DoctrineHelper,
	 BundleNameFromClassName,
	 ContainerAware,
	 UpdateOneVersionOneMethod;

	public function update_1_1_0()
	{
		$this->_executeSQL('
			CREATE TABLE versions_patchs (
				bundle VARCHAR(100) NOT NULL,
				date DATETIME NOT NULL,
				PRIMARY KEY(bundle, date)
			) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
		);
	}

}