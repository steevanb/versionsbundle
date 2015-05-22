<?php

namespace kujaff\VersionsBundle\Service\Install;

use kujaff\VersionsBundle\Model\Update as BaseUpdate;
use kujaff\VersionsBundle\Model\DoctrineHelper;
use kujaff\VersionsBundle\Model\BundleNameFromClassName;
use kujaff\VersionsBundle\Model\UpdateOneVersionOneMethod;
use Symfony\Component\DependencyInjection\ContainerAware;

class Update extends ContainerAware implements BaseUpdate
{
    use DoctrineHelper;
    use BundleNameFromClassName;
    use UpdateOneVersionOneMethod;

    /**
     * Update to 1.1.0
     */
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
