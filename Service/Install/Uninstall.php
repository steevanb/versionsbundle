<?php

namespace kujaff\VersionsBundle\Service\Install;

use kujaff\VersionsBundle\Model\Uninstall as BaseUninstall;
use kujaff\VersionsBundle\Model\DoctrineHelper;
use kujaff\VersionsBundle\Model\BundleNameFromClassName;
use Symfony\Component\DependencyInjection\ContainerAware;

class Uninstall extends ContainerAware implements BaseUninstall
{

    use DoctrineHelper,
        BundleNameFromClassName;

    public function uninstall()
    {
        $this->_dropTables(array('versions_bundles'));
    }
}