<?php

namespace kujaff\VersionsBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use kujaff\VersionsBundle\Entity\Version as VersionEntity;

/**
 * Service for BundleVersions
 */
class Version extends ContainerAware
{

    /**
     * Return -1 if $version1 < $version2, 0 if $version1 = $version2, +1 if $version1 > $version2
     *
     * @param mixed $version1 Can be a string (X.Y.Z) or a Version instance
     * @param mixed $version2 Can be a string (X.Y.Z) or a Version instance
     */
    public function compare($version1, $version2)
    {
        if (is_string($version1)) {
            $version1 = new VersionEntity($version1);
        }
        if (is_string($version2)) {
            $version2 = new VersionEntity($version2);
        }

        $lengthMajor = max(strlen($version1->getMajor()), strlen($version2->getMajor()));
        $lengthMinor = max(strlen($version1->getMinor()), strlen($version2->getMinor()));
        $lengthPatch = max(strlen($version1->getPatch()), strlen($version2->getPatch()));
        $version1Number = sprintf('%0' . $lengthMajor . 's', $version1->getMajor()) . sprintf('%0' . $lengthMinor . 's', $version1->getMinor()) . sprintf('%0' . $lengthPatch . 's', $version1->getPatch());
        $version2Number = sprintf('%0' . $lengthMajor . 's', $version2->getMajor()) . sprintf('%0' . $lengthMinor . 's', $version2->getMinor()) . sprintf('%0' . $lengthPatch . 's', $version2->getPatch());

        if ($version1Number == $version2Number) {
            return 0;
        } else {
            return ($version1Number > $version2Number) ? 1 : -1;
        }
    }
}
