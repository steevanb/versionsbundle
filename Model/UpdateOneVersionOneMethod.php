<?php

namespace kujaff\VersionsBundle\Model;

use kujaff\VersionsBundle\Entity\BundleVersion;
use kujaff\VersionsBundle\Entity\Version;

/**
 * Search for methods update_X_Y_Z() and call it
 */
trait UpdateOneVersionOneMethod
{

    /**
     * Find update methods (syntax : update_X_Y_Z())
     *
     * @param UpdateInterface $object
     * @return array
     */
    protected function findUpdateMethods(UpdateInterface $object)
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
     * @param UpdateInterface $updater Use $this in your class
     * @param BundleVersion $bundleVersion
     * @param Version $version Update to this version
     * @return Version
     */
    protected function updateOneVersionOneMethod(UpdateInterface $updater, BundleVersion $bundleVersion, Version $version)
    {
        $methods = $this->findUpdateMethods($updater);
        $service = $this->container->get('versions.version');
        foreach ($methods as $method) {
            if ($service->compare($method, $version) == 1) {
                break;
            }
            if ($service->compare($method, $bundleVersion->getInstalledVersion()) == 1 && $service->compare($method, $bundleVersion->getVersion()) <= 0) {
                $this->{'update_' . str_replace('.', '_', $method)}();
            }
        }
        return $version;
    }

    /**
     * Update bundle
     *
     * @param BundleVersion $bundleVersion
     * @param Version $version Update to this version
     * @return Version
     */
    public function update(BundleVersion $bundleVersion, Version $version)
    {
        return $this->updateOneVersionOneMethod($this, $bundleVersion, $version);
    }
}
