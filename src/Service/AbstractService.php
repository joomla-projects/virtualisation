<?php
/**
 * Part of the Joomla Testing Framework Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Testing\Service;

use Joomla\Testing\ServerConfig;

/**
 * Class AbstractService
 *
 * @package  Joomla\Testing
 * @since    __DEPLOY_VERSION__
 */
abstract class AbstractService implements Service
{
	/**
	 * @var ServerConfig[]
	 */
	protected $configs;

	public function __construct(ServerConfig $config)
	{
		$this->setConfiguration($config);
	}

	public function setConfiguration(ServerConfig $config)
	{
		$this->configs[] = $config;
	}
}
