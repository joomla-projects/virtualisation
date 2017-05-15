<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

/**
 * Class PhpFpm
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class PhpFpm extends PhpBase
{
	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name               = 'php-' . $this->version;
		$dockerPath         = 'docker/' . $name;
		$this->setup[$name] = [
			'build'   => $dockerPath,
			'volumes' => [
				getcwd() . '/vendor:/usr/local/lib/php/vendor',
			],
			'links'   => [],
		];

		foreach ($this->configs as $config)
		{
			$driver                        = Map::getType($config->get('database.driver'));
			$version                       = $config->getVersion('database');
			$this->setup[$name]['links'][] = "$driver-$version";

			$domain                          = $config->getDomain();
			$server                          = $config->get('server.type');
			$version                         = $server == 'apache' ? $config->getVersion('php') : $config->getVersion('server');
			$this->setup[$name]['volumes'][] = getcwd() . "/$this->dockyard/docker/$server-$version/html/$domain:/var/www/html/$domain";
		}

		$this->setup[$name]['links'] = array_unique($this->setup[$name]['links']);

		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$dockerPath = $this->dockyard . '/docker/php-' . $this->version;

		$this->createDockerfile($dockerPath, __DIR__ . '/docker/php-fpm');
	}
}
