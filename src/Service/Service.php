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
 * Interface Service
 *
 * @package  Joomla\Testing
 * @since    __DEPLOY_VERSION__
 */
interface Service
{
	/**
	 * Set a new configuration
	 *
	 * @param   ServerConfig  $config  The configuration
	 *
	 * @return  void
	 */
	public function setConfiguration(ServerConfig $config);

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup();
}
