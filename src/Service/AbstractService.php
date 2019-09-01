<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;

/**
 * Class AbstractService
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
abstract class AbstractService implements Service
{
	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var ServerConfig[]
	 */
	protected $configs;

	/**
	 * @var  string
	 */
	protected $dockyard;

	/**
	 * @var Service[]
	 */
	protected $services;

	protected $network;

	public function __construct($version, ServerConfig $config)
	{
		$this->version  = $version;
		$this->dockyard = $config->get('host.dockyard');
		$this->network  = $config->get('network.name');
		$this->addConfiguration($config);
	}

	public function addConfiguration(ServerConfig $config)
	{
		$this->configs[] = $config;
	}

	public function addService(Service $service)
	{
		$this->services[] = $service;
	}

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$setup = [];
		foreach ((array) $this->services as $service)
		{
			$setup = array_merge($setup, $service->getSetup());
		}

		return $setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		foreach ((array) $this->services as $service)
		{
			$service->prepare();
		}
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	protected function fixName($name)
	{
		return strtolower(str_replace(['-', '.'], ['v', 'p'], $name));
	}
}
