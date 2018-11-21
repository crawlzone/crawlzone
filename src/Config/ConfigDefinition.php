<?php
declare(strict_types=1);

namespace Crawlzone\Config;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @package Crawlzone\Config
 */
class ConfigDefinition implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('crawler');

        $node->children()
            ->scalarNode('start_uri')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->arrayNode('start_uri')
                ->info('A list of URIs to crawl.')
                ->scalarPrototype()->end()
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->integerNode('concurrency')
                ->min(1)
                ->defaultValue(10)
            ->end()
            ->integerNode('depth')
                ->min(1)
                ->info('The maximum depth that will be allowed to crawl.')
            ->end()
            ->scalarNode('save_progress_in')
                ->defaultValue('memory')
            ->end()
            ->append($this->autoThrottle())
            ->append($this->filterOptions())
            ->append($this->requestOptions())
        ->end();

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    private function autoThrottle(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('autothrottle');

        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('enabled')
                ->info('Enables autothrottle extension.')
                ->defaultTrue()
            ->end()
            ->integerNode('min_delay')
                ->info('Sets minimum delay between the requests.')
                ->defaultValue(0)
            ->end()
            ->integerNode('max_delay')
                ->info('Sets maximun delay between the requests.')
                ->defaultValue(60)
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function filterOptions(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('filter');

        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('robotstxt_obey')
                ->info('If enabled, crawler will respect robots.txt policies.')
                ->defaultValue(false)
            ->end()
            ->arrayNode('allow')
                ->info('A list of regular expressions that the urls must match in order to be extracted. If not given (or empty), it will match all links.')
                ->scalarPrototype()->end()
                ->defaultValue([])
            ->end()
            ->arrayNode('allow_domains')
                ->info('A list of string containing domains which will be considered for extracting the links.')
                ->scalarPrototype()->end()
                ->defaultValue([])
            ->end()
            ->arrayNode('deny_domains')
                ->info('A list of strings containing domains which won’t be considered for extracting the links. It has precedence over the allow_domains parameter.')
                ->scalarPrototype()->end()
                ->defaultValue([])
            ->end()
            ->arrayNode('deny')
                ->info('A list of regular expressions) that the urls must match in order to be excluded (ie. not extracted). It has precedence over the allow parameter.')
                ->scalarPrototype()->end()
                ->defaultValue([])
            ->end()
        ->end();

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function requestOptions(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('request_options');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('verify')
                    ->info('Describes the SSL certificate verification behavior of a request.')
                    ->defaultTrue()
                ->end()
                ->booleanNode('cookies')
                    ->info('Specifies whether or not cookies are used in a request or what cookie jar to use or what cookies to send.')
                    ->defaultTrue()
                ->end()
                ->booleanNode('allow_redirects')
                    ->info('Describes the redirect behavior of a request.')
                    ->defaultFalse()
                ->end()
                ->booleanNode('debug')
                    ->info('Set to true or to enable debug output with the handler used to send a request.')
                    ->defaultFalse()
                ->end()
                ->floatNode('connect_timeout')
                    ->info('Float describing the number of seconds to wait while trying to connect to a server. Use 0 to wait indefinitely (the default behavior).')
                ->end()
                ->floatNode('timeout')
                    ->info('Float describing the timeout of the request in seconds. Use 0 to wait indefinitely (the default behavior).')
                ->end()
                ->floatNode('delay')
                    ->info('The number of milliseconds to delay before sending the request.')
                ->end()
                ->booleanNode('decode_content')
                    ->info('Specify whether or not Content-Encoding responses (gzip, deflate, etc.) are automatically decoded.')
                ->end()
                ->scalarNode('force_ip_resolve')
                    ->info('Set to "v4" if you want the HTTP handlers to use only ipv4 protocol or "v6" for ipv6 protocol.')
                ->end()
                // Example: 'proxy' => [
                //    'http'  => 'tcp://localhost:8125', // Use this proxy with "http"
                //    'https' => 'tcp://localhost:9124', // Use this proxy with "https",
                //    'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
                // ]
                ->variableNode('proxy')
                    ->info('Pass an array to specify different proxies for different protocols.')
                ->end()
                // ['cert' => ['/path/server.pem', 'password']]
                ->variableNode('cert')
                    ->info('Set to an array to specify the path to a file containing a PEM formatted client side certificate and password.')
                ->end()
                // ['ssl_key' => ['/path', 'password']]
                ->variableNode('ssl_key')
                    ->info('Specify the path to a file containing a private SSL key in PEM format.')
                ->end()
                ->floatNode('read_timeout')
                    ->info('Float describing the timeout to use when reading a streamed body. Defaults to the value of the default_socket_timeout PHP ini setting')
                ->end()
                ->booleanNode('stream')
                    ->info('Set to true to stream a response rather than download it all up-front.')
                ->end()
                ->scalarNode('version')
                    ->info('Protocol version to use with the request.')
                ->end()

            ->end()
        ->end();

        return $node;
    }
}
