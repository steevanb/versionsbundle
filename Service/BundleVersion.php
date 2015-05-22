<?php

namespace kujaff\VersionsBundle\Service;

use kujaff\VersionsBundle\Entity\BundleVersion as BundleVersionEntity;
use Symfony\Component\DependencyInjection\ContainerAware;
use kujaff\VersionsBundle\Model\VersionnedBundle;

/**
 * Service for BundleVersions
 */
class BundleVersion extends ContainerAware
{

    /**
     * Get bundle version informations
     *
     * @param string $bundle
     * @return BundleVersionEntity
     */
    public function getVersion($bundle)
    {
        $doctrine = $this->container->get('doctrine');
        $return = $doctrine->getRepository('VersionsBundle:BundleVersion')->findOneByName($bundle);
        if ($return === null) {
            $return = new BundleVersionEntity($bundle);
        }
        $bundle = $this->container->get('kernel')->getBundle($bundle);
        if ($bundle instanceof VersionnedBundle) {
            $return->setVersion($bundle->getVersion());
        }
        return $return;
    }

    /**
     * Get versionned bundles
     *
     * @return BundleVersionEntity[]
     */
    public function getBundles()
    {
        $return = array();
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            if ($bundle instanceof VersionnedBundle) {
                $return[] = $this->getVersion($bundle->getName());
            }
        }
        return $return;
    }
}
