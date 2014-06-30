<?php

namespace kujaff\VersionsBundle;

use kujaff\VersionsBundle\Model\VersionnedBundle;
use kujaff\VersionsBundle\DependencyInjection\Compiler\TaggedServicesPass;
use kujaff\VersionsBundle\Entity\Version;

class VersionsBundle extends VersionnedBundle
{

    public function __construct()
    {
        $this->version = new Version('1.1.0');
    }

    public function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new TaggedServicesPass());
    }
}