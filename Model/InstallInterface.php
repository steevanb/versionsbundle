<?php

namespace kujaff\VersionsBundle\Model;

/**
 * Interface to implements to install bundle
 */
interface InstallInterface
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
