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

	public function __construct($version, ServerConfig $config)
	{
		$this->version  = $version;
		$this->dockyard = $config->get('host.dockyard');
		$this->addConfiguration($config);
	}

	public function addConfiguration(ServerConfig $config)
	{
		$this->configs[] = $config;
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
