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
 * Interface Service
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
interface Service
{
	/**
	 * Set a new configuration
	 *
	 * @param   ServerConfig $config The configuration
	 *
	 * @return  void
	 */
	public function addConfiguration(ServerConfig $config);

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup();

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare();
}
