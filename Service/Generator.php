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
	 * Return a ClassGenerator
	 *
	 * @param BaseBundle $bundleInfos
	 * @param string $class Class name
	 * @param string $interface Interface to use, just class name
	 * @return ClassGenerator
	 */
	protected function _initGenerator(BaseBundle $bundleInfos, $class, $interface)
	{
		$return = new ClassGenerator();
		$return->setClassName($class);
		$return->setNamespace($bundleInfos->getNamespace() . '\Service\Install');
		$return->setTraits(array(
			'kujaff\VersionsBundle\Model\BundleNameFromClassName',
			'kujaff\VersionsBundle\Model\DoctrineHelper',
		));
		$return->setExtends('Symfony\Component\DependencyInjection\ContainerAware');
		$return->addUse('kujaff\VersionsBundle\Model\\' . $interface, 'Base' . $interface);
		$return->addInterface('Base' . $interface);
		$return->setConcatTraits(true);

		return $return;
	}

	/**
	 * Register an installer service
	 *
	 * @param BaseBundle $bundleInfos
	 * @param string $type Type (install, update or uninstall)
	 * @param string $class Class (Install, Update or Uninstall)
	 */
	protected function _registerInstallerService(BaseBundle $bundleInfos, $type, $class)
	{
		$serviceId = strtolower($bundleInfos->getName()) . '.installer.' . $type;
		$fullyQualifiedClass = $bundleInfos->getNamespace() . '\Service\Install\\' . $class;
		$serviceOptions = array(
			'calls' => array(array('setContainer' => array('@service_container'))),
			'tags' => array(array('name' => 'bundle.' . $type))
		);
		$this->registerService($bundleInfos->getName(), $serviceId, $fullyQualifiedClass, $serviceOptions);
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
	 * @param string $versionAfterInstallation Version after installation, ex '1.0.0'
	 * @param string $updateTrait Trait to use in Update service, like kujaff\VersionsBundle\Model\UpdateOneVersionOneMethod
	 */
	public function generate($bundle, $versionAfterInstallation, $updateTrait = null, $force = false)
	{
		$this->generateInstallService($bundle, $versionAfterInstallation, $force);
		$this->generateUpdateService($bundle, $updateTrait, $force);
		$this->generateUninstallService($bundle, $force);
	}

	/**
	 * Generate service and register it for installation
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 * @param string $version Version after installation, ex '1.0.0'
	 * @param boolean $force Indicate if you want to regenerate it although it exists
	 * @return boolean
	 */
	public function generateInstallService($bundle, $version, $force = false)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);
		// do not create service if another one is already registered
		if ($this->existsTaggedService($bundle, 'bundle.install') && $force == false) {
			return false;
		}

		$generator = $this->_initGenerator($bundleInfos, 'Install', 'Install');

		$generator->startMethod('install', ClassGenerator::VISIBILITY_PUBLIC, false, array('Installation'), 'kujaff\VersionsBundle\Entity\Version');
		$generator->addMethodLine($generator->getCode4Comment('Do your stuff here'));
		$generator->addMethodLine($generator->getCode4Line('return new Version(\'' . $version . '\');', 0, 0));
		$generator->finishMethod();

		$generator->write($bundleInfos->getPath() . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'Install.php');

		$this->_registerInstallerService($bundleInfos, 'install', 'Install');

		return true;
	}

	public function generateUpdateService($bundle, $trait = null, $force = false)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);
		// do not create service if another one is already registered
		if ($this->existsTaggedService($bundle, 'bundle.update') && $force == false) {
			return false;
		}

		$generator = $this->_initGenerator($bundleInfos, 'Update', 'Update');

		if ($trait !== null) {
			$generator->addTrait($trait);
		} else {
			$generator->startMethod('update', ClassGenerator::VISIBILITY_PUBLIC, false, array('Updates'), 'kujaff\VersionsBundle\Entity\Version');
			$generator->addMethodParameter('bundleVersion', 'kujaff\VersionsBundle\Entity\BundleVersion', null, true, 'Current installed version');
			$generator->addMethodParameter('version', 'kujaff\VersionsBundle\Entity\Version', null, true, 'Update to this version');
			$generator->addMethodLine($generator->getCode4Comment('Do your stuff here'));
			$generator->addMethodLine($generator->getCode4Comment('Return updated version after your patchs', 0, 0));
			$generator->addMethodLine($generator->getCode4Line('return $version;', 0, 0));
			$generator->finishMethod();
		}

		$generator->write($bundleInfos->getPath() . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'Update.php');

		$this->_registerInstallerService($bundleInfos, 'update', 'Update');

		return true;
	}

	/**
	 * Generate service and register it for uninstall
	 *
	 * @param string $bundle Name of your bundle, ex 'FooBundle'
	 * @param boolean $force Indicate if you want to regenerate it although it exists
	 * @return boolean
	 */
	public function generateUninstallService($bundle, $force = false)
	{
		$bundleInfos = $this->_getBundleInformations($bundle);
		// do not create service if another one is already registered
		if ($this->existsTaggedService($bundle, 'bundle.uninstall') && $force == false) {
			return false;
		}

		$generator = $this->_initGenerator($bundleInfos, 'Uninstall', 'Uninstall');

		$generator->startMethod('uninstall', ClassGenerator::VISIBILITY_PUBLIC, false, array('Uninstall'));
		$generator->addMethodLine($generator->getCode4Comment('Do your stuff here', 0, 0));
		$generator->finishMethod();

		$generator->write($bundleInfos->getPath() . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'Uninstall.php');

		$this->_registerInstallerService($bundleInfos, 'uninstall', 'Uninstall');

		return true;
	}

}