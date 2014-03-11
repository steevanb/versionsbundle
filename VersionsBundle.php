<?php
namespace kujaff\VersionsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use kujaff\VersionsBundle\DependencyInjection\Compiler\TaggedServicesPass;

class VersionsBundle extends Bundle
{

	public function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new TaggedServicesPass());
	}

}