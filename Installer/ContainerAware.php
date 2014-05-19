<?php
namespace kujaff\VersionsBundle\Installer;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define a protected property ContainerInterface $container and define it in __construct(ContainerInterface $container)
 */
trait ContainerAware
{
	/**
	 * Container
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

}