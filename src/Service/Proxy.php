<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

/**
 * Class Apache
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Proxy extends AbstractService
{
	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$config = $this->configs[0];
		$port = $config->get('host.port');

		return [
			'proxy' => [
				'image'   => 'jwilder/nginx-proxy:alpine',
				'ports'   => ["$port:80"],
				'volumes' => ['/var/run/docker.sock:/tmp/docker.sock:ro'],
			]
		];
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
	}
}
