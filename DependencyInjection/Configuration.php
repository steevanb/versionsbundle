<?php

namespace kujaff\VersionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('versions');

        // install order
        $rootNode->children()->arrayNode('installOrder')->prototype('array')->children()
            ->scalarNode('force')->defaultFalse()->end()
            ->end()->end()->end();

        // update order
        $rootNode->children()->arrayNode('updateOrder')->prototype('array')->children()
            ->scalarNode('bundle')->isRequired()->end()
            ->scalarNode('version')->isRequired()->end()
            ->end()->end()->end();

        // check needInstallation
        $rootNode->children()->booleanNode('checkNeedInstallation')->defaultTrue()->end();

        // check needUpToDate
        $rootNode->children()->booleanNode('checkNeedUpToDate')->defaultTrue()->end();

        return $treeBuilder;
    }
}
