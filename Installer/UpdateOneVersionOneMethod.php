<?php

namespace kujaff\VersionsBundle\Installer;

use kujaff\VersionsBundle\Entity\BundleVersion;
use kujaff\VersionsBundle\Versions\Version;

/**
 * Search for methods update_X_Y_Z() and call it
 */
trait UpdateOneVersionOneMethod
{

    /**
     * Find update methods (syntax : update_X_Y_Z())
     *
     * @param Update $object
     */
    protected function _findUpdateMethods(Update $object)
    {
        $return = array();
        foreach (get_class_methods(get_class($object)) as $method) {
            if (substr($method, 0, 7) == 'update_') {
                $version = substr($method, 7);
                if (preg_match('/[0-9]{1,}[_]{1}[0-9]{1,}[_]{1}[0-9]{1,}/', $version)) {
                    $return[] = str_replace('_', '.', $version);
                }
            }
        }
        sort($return);
        return $return;
    }

    /**
     * Update bundle
     *
     * @param Update $updater Use $this in your class
     * @param BundleVersion $bundleVersion
     * @param Version $version Update to this version
     */
    protected function _updateOneVersionOneMethod(Update $updater, BundleVersion $bundleVersion, Version $version)
    {
        $methods = $this->_findUpdateMethods($updater);
        $service = $this->container->get('bundle.version');
        foreach ($methods as $method) {
            if ($service->compare($method, $version) == 1) {
                continue;
            }
            if ($service->compare($method, $bundleVersion->getInstalledVersion()) == 1 && $service->compare($method, $bundleVersion->getVersion()) <= 0) {
                $this->{'update_' . str_replace('.', '_', $method)}();
            }
        }
        return $bundleVersion->getVersion();
    }

    /**
     * Update bundle
     *
     * @param BundleVersion $bundleVersion
     * @param Version $version Update to this version
     */
    public function update(BundleVersion $bundleVersion, Version $version)
    {
        return $this->_updateOneVersionOneMethod($this, $bundleVersion, $version);
    }
}