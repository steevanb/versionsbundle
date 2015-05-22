<?php

namespace kujaff\VersionsBundle\Model;

use kujaff\VersionsBundle\Entity\BundleVersion;
use kujaff\VersionsBundle\Entity\Version;

/**
 * Update a bundle
 */
interface UpdateInterface
{

    /**
     * Get bundle name
     */
    public function getBundleName();

    /**
     * Update bundle
     *
     * @param BundleVersion $bundleVersion
     */
    public function update(BundleVersion $bundleVersion, Version $version);
}
