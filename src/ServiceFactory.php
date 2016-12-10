<?php
/**
 * Part of the Joomla Testing Framework Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Testing;

use Joomla\Testing\Service\Service;

/**
 * Class ServiceFactory
 *
 * Creates the YAML stubs (configuration) for the docker compose file
 *
 * @package  Joomla\Testing
 * @since    __DEPLOY_VERSION__
 */
class ServiceFactory
{
	/**
	 * @var  ServerConfig  Configuration
	 */
	private $config;

	private $mapping = [
		'apache'     => '\\Joomla\\Testing\\Service\\Apache',
		'nginx'      => '\\Joomla\\Testing\\Service\\Nginx',
		'postgresql' => '\\Joomla\\Testing\\Service\\PostgreSql',
		'mysql'      => '\\Joomla\\Testing\\Service\\MySql',
		'php'        => '\\Joomla\\Testing\\Service\\PhpFpm',
	];

	/**
	 * @var Service[][]
	 */
	private $cache;

	public function getWebserver()
	{
		return $this->getService($this->config->get('server.type'), $this->config->get('server.version'));
	}

	public function getDatabaseServer()
	{
		return $this->getService($this->config->get('database.driver'), $this->config->get('database.version'));
	}

	public function getPhpServer()
	{
		return $this->getService('php', $this->config->get('php.version'));
	}

	/**
	 * @param $server
	 *
	 * @return mixed
	 */
	private function getService($server, $version)
	{
		if (empty($version)) {
			$version = 'latest';
		}

		if (isset($this->cache[$server][$version])) {
			$this->cache[$server][$version]->setConfiguration($this->config);

			return $this->cache[$server][$version];
		}

		if (!isset($this->mapping[$server])) {
			throw new \RuntimeException("Unknown Service $server");
		}

		$serviceClass = $this->mapping[$server];

		$this->cache[$server][$version] = new $serviceClass($this->config);

		return $this->cache[$server][$version];
	}

	/**
	 * @param   ServerConfig $config Configuration
	 */
	public function setConfiguration($config)
	{
		$this->config = $config;
	}
}
