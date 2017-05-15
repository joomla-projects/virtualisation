<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation;

use Joomla\Virtualisation\Service\Map;
use Joomla\Virtualisation\Service\Service;

/**
 * Class ServiceFactory
 *
 * Creates the YAML stubs (configuration) for the docker compose file
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class ServiceFactory
{
	/**
	 * @var  ServerConfig  Configuration
	 */
	private $config;

	/**
	 * @var Service[][]
	 */
	private $cache;

	/**
	 * @param   ServerConfig $config Configuration
	 */
	public function setConfiguration($config)
	{
		$this->config = $config;
	}

	public function getWebserver()
	{
		return $this->getService($this->config->get('server.type'), $this->config->getVersion('server'));
	}

	/**
	 * @param $server
	 *
	 * @return mixed
	 */
	private function getService($server, $version)
	{
		$service = Map::getClass($server);

		if (empty($version))
		{
			$version = 'latest';
		}

		if (isset($this->cache[$service][$version]))
		{
			$this->cache[$service][$version]->addConfiguration($this->config);

			return $this->cache[$service][$version];
		}

		$this->cache[$service][$version] = new $service($version, $this->config);

		return $this->cache[$service][$version];
	}

	public function getProxyServer()
	{
		return $this->getService('proxy', 'default');
	}

	public function getDatabaseServer()
	{
		return $this->getService($this->config->get('database.driver'), $this->config->getVersion('database'));
	}

	public function getPhpServer()
	{
		if ($this->config->get('server.type') == 'apache')
		{
			return null;
		}
		return $this->getService('php', $this->config->getVersion('php'));
	}

	public function getApplication()
	{
		return $this->getService('joomla', $this->config->getVersion('joomla'));
	}
}
