<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation;

use Greencape\PhpVersions;
use Joomla\Virtualisation\Service\Service;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerComposeGenerator
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class DockerComposeGenerator
{
    private $servers = [];
    /**
     * @var  string
     */
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function write($filename)
    {
        $xmlFiles = array_diff(scandir($this->path), ['.', '..', 'database.xml', 'default.xml']);

        return file_put_contents($filename, Yaml::dump($this->getCombinedSetup($this->getServices($xmlFiles)), 4, 2));
    }

    /**
     * @param Service[] $servers
     *
     * @return array
     */
    protected function getCombinedSetup($servers)
    {
        $fixName = function ($name) {
            return strtolower(str_replace(['-', '.'], ['v', 'p'], $name));
        };
        $contents = [];

        foreach ($servers as $server) {
            $server->prepare();

            foreach ($server->getSetup() as $name => $setup) {
                if (isset($setup['links'])) {
                    $setup['links'] = array_map($fixName, $setup['links']);
                }

                $contents[$fixName($name)] = $setup;
            }
        }

        return array_filter($contents);
    }

    /**
     * @param $xmlFiles
     *
     * @return Service[]
     */
    protected function getServices($xmlFiles)
    {
        $this->servers = [];
        $factory = new ServiceFactory();

        foreach ($xmlFiles as $file) {
            $factory->setConfiguration(new ServerConfig($this->path . '/' . $file));

            $this->registerServer($factory->getWebserver());
            $this->registerServer($factory->getDatabaseServer());
            $this->registerServer($factory->getPhpServer());
            $this->registerServer($factory->getApplication());
        }

        return $this->servers;
    }

    /**
     * @param $server
     */
    protected function registerServer($server)
    {
        if (empty($server)) {
            return;
        }

        $this->servers[spl_object_hash($server)] = $server;
    }

    private function refreshVersionCache()
    {
        $versions = new PhpVersions(null, PhpVersions::VERBOSITY_NORMAL | PhpVersions::CACHE_DISABLED);
    }
}
