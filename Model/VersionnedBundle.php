<?php

namespace kujaff\VersionsBundle\Model;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use kujaff\VersionsBundle\Exception\VersionException;

/**
 * Extends this class instead of Bundle SF2 class to add getVersion
 */
class VersionnedBundle extends Bundle
{
    /**
     * Version
     *
     * @var Version
     */
    protected $version;

    /**
     * Indicate if bundle needs to be installed or if it can be used without installation
     *
     * @var boolean
     */
    protected $needInstallation = true;

    /**
     * Indicate if bundle needs to be up to date or if it can be used without being up to date
     *
     * @var boolean
     */
    protected $needUpToDate = true;

    /**
     * Boot the bundle
     *
     * @throws Exception
     */
    public function boot()
    {
        parent::boot();

        if (php_sapi_name() == 'cli') {
            return null;
        }

        $bundleVersion = null;

        // need to be installed
        if ($this->getNeedInstallation() && $this->container->getParameter('versions.checkNeedInstallation')) {
            $bundleVersion = $this->container->get('versions.bundle')->getVersion($this->getName());
            if ($bundleVersion->isInstalled() === false) {
                if ($this->getName() == 'VersionsBundle') {
                    $message = 'Bundle "' . $this->getName() . '" needs to be installed. Exec "php app/console bundle:install ' . $this->getName() . ' --force".';
                } else {
                    $message = 'Bundle "' . $this->getName() . '" needs to be installed. Exec "php app/console bundle:install ' . $this->getName() . '".';
                }
                throw new VersionException($message);
            }
        }

        // need to be updated
        if ($this->getNeedUpToDate() && $this->container->getParameter('versions.checkNeedUpToDate')) {
            if ($bundleVersion === null) {
                $bundleVersion = $this->container->get('versions.bundle')->getVersion($this->getName());
            }
            if ($bundleVersion->needUpdate()) {
                throw new VersionException('Bundle "' . $this->getName() . '" needs to be updated. Exec "php app/console bundle:update ' . $this->getName() . '".');
            }
        }
    }

    /**
     * Return version
     *
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Define if bundle needs to be installed
     *
     * @param boolean $needInstallation
     * @return VersionnedBundle
     */
    public function setNeedInstallation($needInstallation)
    {
        $this->needInstallation = $needInstallation;
        return $this;
    }

    /**
     * Get if bundle needs to be installed
     *
     * @return boolean
     */
    public function getNeedInstallation()
    {
        return $this->needInstallation;
    }

    /**
     * Define if bundle needs to be up-to-date
     *
     * @param boolean $needUpToDate
     * @return VersionnedBundle
     */
    public function setNeedUpToDate($needUpToDate)
    {
        $this->needUpToDate = $needUpToDate;
        return $this;
    }

    /**
     * Get if bundle needs to be up-to-date
     *
     * @return boolean
     */
    public function getNeedUpToDate()
    {
        return $this->needUpToDate;
    }
}
