<?php

namespace kujaff\VersionsBundle;

use kujaff\VersionsBundle\Model\VersionnedBundle;
use kujaff\VersionsBundle\DependencyInjection\Compiler\TaggedServicesPass;
use kujaff\VersionsBundle\Entity\Version;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VersionsBundle extends VersionnedBundle
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->version = new Version('2.0.1');
    }

    /**
     * @inherited
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TaggedServicesPass());
    }
}
