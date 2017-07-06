<?php

namespace Nicomak\PaypalBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nicomak_paypal');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode->children()
                    ->scalarNode('email')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('confirm_route')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('cancel_route')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('item_name')->cannotBeEmpty()->defaultValue(null)->end()
                    ->scalarNode('item_number')->cannotBeEmpty()->defaultValue(null)->end()
                    ->integerNode('quantity')->cannotBeEmpty()->defaultValue(1)->end()
                    ->booleanNode('debug')->cannotBeEmpty()->defaultValue(false)->end()
            ->end();

        return $treeBuilder;
    }
}
