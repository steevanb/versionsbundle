<?php
namespace kujaff\VersionsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Save service tagged "bundle.install" and "bundle.update"
 */
class TaggedServicesPass implements CompilerPassInterface
{

	/**
	 * Save tagged service into a cache file
	 *
	 * @param ContainerBuider $container
	 * @param string $tag
	 * @param string $fileName
	 */
	private function _saveTaggedServices($container, $tag, $fileName)
	{
		$services = array();
		foreach ($container->findTaggedServiceIds($tag) as $id => $attributes) {
			$services[] = $id;
		}
		$php = '<?php return ' . var_export($services, true) . ';';
		file_put_contents($container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . $fileName, $php);
	}

	/**
	 * Process the build
	 *
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		$this->_saveTaggedServices($container, 'bundle.install', 'services.bundle.install.php');
		$this->_saveTaggedServices($container, 'bundle.update', 'services.bundle.update.php');
		$this->_saveTaggedServices($container, 'bundle.uninstall', 'services.bundle.uninstall.php');
	}

}