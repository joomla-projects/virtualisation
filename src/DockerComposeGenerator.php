<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation;

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
		$fixName  = function ($name) {
			return str_replace(['-', '.'], ['V', 'p'], $name);
		};
		$contents = [];

		foreach ($servers as $server)
		{
			$server->prepare();

			foreach ($server->getSetup() as $name => $setup)
			{
				if (isset($setup['links']))
				{
					$setup['links'] = array_map($fixName, $setup['links']);
				}

				$contents[$fixName($name)] = $setup;
			}
		}

		return $contents;
	}

	/**
	 * @param $xmlFiles
	 *
	 * @return Service[]
	 */
	protected function getServices($xmlFiles)
	{
		$servers = [];
		$factory = new ServiceFactory();

		foreach ($xmlFiles as $file)
		{
			$factory->setConfiguration(new ServerConfig($this->path . '/' . $file));

			$server                            = $factory->getWebserver();
			$servers[spl_object_hash($server)] = $server;

			$server                            = $factory->getDatabaseServer();
			$servers[spl_object_hash($server)] = $server;

			$server                            = $factory->getPhpServer();
			$servers[spl_object_hash($server)] = $server;

			$server                            = $factory->getApplication();
			$servers[spl_object_hash($server)] = $server;
		}

		return $servers;
	}
}
