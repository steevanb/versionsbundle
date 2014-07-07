<?php

namespace kujaff\VersionsBundle\Service;

use steevanb\UtilsBundle\Model\ContainerAware;
use kujaff\VersionsBundle\Model\BundleInformations;
use steevanb\CodeGenerator\PHP\ClassGenerator;
use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;
use Symfony\Component\Yaml\Yaml;

/**
 * Generate files to make bundle versionned
 */
class Generator
{

	use ContainerAware,
	 BundleInformations;

	/**
	 * Return services.yml file path
	 *
	 * @param BaseBundle $bundle
	 * @return string
	 */
	protected function _getYamlFilePath(BaseBundle $bundle)
	{
		return $bundle->getPath() . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'services.yml';
	}

	/**
	 * Parse Yaml service declaration
	 *
	 * @param BaseBundle $bundle
	 * @return array
	 */
	protected function _parseServicesYaml(BaseBundle $bundle)
	{
		$servicesFilePath = $this->_getYamlFilePath($bundle);
		$return = array();
		if (file_exists($servicesFilePath)) {
			$return = Yaml::parse(file_get_contents($servicesFilePath));
			if ($return === null) {
				$return = array();
			}
		}
		if (array_key_exists('services', $return) == false || is_array($return['services']) == false) {
			$return['services'] = array();
		}
		return $return;
	}

	/**
	 * Register a new service in Resources/config/services.yml
	 *
	 * @param string $bundle Bundle name, ex 'FooBundle'
	 * @param string $service Service name, ex 'foobundle.service'
	 * @param string $class Fully qualified class name, ex 'Foo\Bar\ClassName'
	 * @param array $options Options, ex array('arguments' => array('@service_container'), 'tags' => array(array('name' => 'bundle.install'))
	 */
	public function registerService($bundle, $service, $class, $options = array())
	{
		$bundleInfos = $this->_getBundleInformations($bundle);
		$services = $this->_parseServicesYaml($bundleInfos);

		$services['services'][$service] = array_merge(array('class' => $class), $options);
		$yamlFilePath = $this->_getYamlFilePath($bundleInfos);
		$yamlContent = Yaml::dump($services, 4);

		$result = file_put_contents($yamlFilePath, $yamlContent);
		if ($result === false) {
			throw new \Exception('Error while writing "' . $yamlFilePath . '", maybe directory or file can\'t be written.');
		}
	}

	/**
	 * Indicate if a tagged service exists in bundle
	 *
	 * @param string $bundle Bundle name, ex 'FooBundle'
	 * @param string $tag Tag name, ex 'bundle.install'
	 * @return boolean
	 */
	public function existsTaggedService($bundle, $tag)
	{
		$services = $this->_parseServicesYaml($this->_getBundleInformations($bundle));
		foreach ($services['services'] as $params) {
			if (array_key_exists('tags', $params) && is_array($params['tags'])) {
				foreach ($params['tags'] as $tagInfos) {
					if (array_key_exists('name', $tagInfos) && $tagInfos['name'] == $tag) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Generate everything to make your bundle versionned
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 * @param string $version Version after installation, ex '1.0.0'
	 */
	public function generate($bundle, $version)
	{
		$this->generateInstallService($bundle, $version);
		$this->generateUninstallService($bundle);
	}

	/**
	 * Generate service and register it for installation
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 * @param string $version Version after installation, ex '1.0.0'
	 */
	public function generateInstallService($bundle, $version)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);
		// do not create service if another one is already registered
		if ($this->existsTaggedService($bundle, 'bundle.install')) {
			//return false;
		}

		$generator = new ClassGenerator();
		$generator->setClassName('Install');
		$generator->setNamespace($bundleInfos->getNamespace() . '\Service\Install');
		$generator->setTraits(array(
			'kujaff\\VersionsBundle\\Model\\BundleNameFromClassName',
			'kujaff\\VersionsBundle\\Model\\DoctrineHelper',
		));
		$generator->setExtends('Symfony\Component\DependencyInjection\ContainerAware');
		$generator->addUse('kujaff\VersionsBundle\Model\Install', 'BaseInstall');
		$generator->addInterface('BaseInstall');
		$generator->setConcatTraits(true);

		$generator->startMethod('install', ClassGenerator::VISIBILITY_PUBLIC, false, array('Installation'), 'kujaff\\VersionsBundle\\Versions\\Version');
		$generator->addMethodLine($generator->getCode4Comment('Do your stuff here'));
		$generator->addMethodLine($generator->getCode4Line('return new Version(\'' . $version . '\');', 0, 0));
		$generator->finishMethod();

		$generator->write($bundleInfos->getPath() . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'Install.php');

		$serviceOptions = array(
			'calls' => array(array('setContainer' => array('@service_container'))),
			'tags' => array(array('name' => 'bundle.install'))
		);
		$this->registerService($bundle, strtolower($bundle) . '.installer.install', $bundleInfos->getNamespace() . '\\Service\\Install\\Install', $serviceOptions);

		return true;
	}

	/**
	 * Generate service and register it for uninstall
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 */
	public function generateUninstallService($bundle)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);

		// do not create service if another one is already registered
		if ($this->existsTaggedService($bundle, 'bundle.uninstall')) {
			return false;
		}

		// generate service PHP code
		$templating = $this->container->get('templating');
		$params = array(
			'bundle' => $bundleInfos->getName(),
			'namespace' => $bundleInfos->getNamespace() . '\\Install',
			'uses' => array(
				'kujaff\\VersionsBundle\\Model\\Uninstall as BaseUninstall',
				'kujaff\\VersionsBundle\\Model\\ContainerAware',
				'kujaff\\VersionsBundle\\Model\\BundleNameFromClassName',
				'kujaff\\VersionsBundle\\Model\\DoctrineHelper'
			),
			'className' => 'Uninstall',
			'implements' => array('BaseUninstall'),
			'traits' => array('ContainerAware', 'BundleNameFromClassName', 'DoctrineHelper')
		);
		$templating->render('VersionsBundle:skeleton:uninstall.php.twig', $params);

		// register service
		$serviceOptions = array(
			'arguments' => array('@service_container'),
			'tags' => array(array('name' => 'bundle.uninstall'))
		);
		$this->registerService($bundle, strtolower($bundle) . '.uninstall', $bundleInfos->getNamespace() . '\\Install\\Uninstall', $serviceOptions);

		return true;
	}

}