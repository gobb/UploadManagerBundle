<?php

/*
 * (c) Florian Koerner <f.koerner@checkdomain.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Checkdomain\UploadManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @author Florian Koerner <f.koerner@checkdomain.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('upload_manager');

        $rootNode->children()
                 ->scalarNode('write_to')->defaultValue('%kernel.root_dir%/../web')->end()
                 ->scalarNode('upload_path')->defaultValue('upload')->end()
                 ->scalarNode('temp_upload_path')->defaultValue('%upload_manager.upload_path%/temp')->end()
                 ->integerNode('temp_upload_lifetime')->defaultValue(10800)->end()
                 ->integerNode('tidy_up_likelihood')->defaultValue(10)->end()
                 ->end();

        return $treeBuilder;
    }
}
