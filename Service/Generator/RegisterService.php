<?php

namespace kujaff\VersionsBundle\Service\Generator;

use kujaff\VersionsBundle\Model\ContainerAware;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

class RegisterService
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
    public function register($bundle, $service, $class, $options = array())
    {
        $bundleInfos = $this->_getBundleInformations($bundle);
        $services = $this->_parseServicesYaml($bundleInfos);

        $services['services'][$service] = array_merge(array('class' => $class), $options);
        $yamlFilePath = $this->_getYamlFilePath($bundleInfos);
        $yamlContent = Yaml::dump($services, 3);

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
    public function existsTagged($bundle, $tag)
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
}