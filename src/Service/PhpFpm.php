<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Greencape\PhpVersions;
use Joomla\Virtualisation\Template;

/**
 * Class PhpFpm
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class PhpFpm extends AbstractService
{
	protected $setup = [];

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name               = 'php-' . $this->version;
		$dockerPath         = $this->dockyard . '/docker/' . $name;
		$this->setup[$name] = [
			'build'   => $dockerPath,
			'volumes' => [
				getcwd() . '/vendor:/usr/local/lib/php/vendor',
			],
			'links'   => [],
		];

		foreach ($this->configs as $config) {
			$driver                        = Map::getType($config->get('database.driver'));
			$version                       = $config->getVersion('database');
			$this->setup[$name]['links'][] = "$driver-$version";

			$domain                          = $config->get('name') . '.' . $config->get('server.tld');
			$server                          = $config->get('server.type');
			$version                         = $server == 'apache' ? $config->getVersion('php') : $config->getVersion('server');
			$this->setup[$name]['volumes'][] = "$this->dockyard/docker/$server-$version/html/$domain:/var/www/html/$domain";
		}

		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$name       = 'php-' . $this->version;
		$dockerPath = $this->dockyard . '/docker/' . $name;

		$phpInfo        = (new PhpVersions())->getInfo($this->version);
		$dockerTemplate = new Template(__DIR__ . '/docker/php-fpm');
		$dockerTemplate->setVariables(
			[
				'php.version'     => $this->version,
				'xdebug.version'  => $phpInfo['xdebug']['version'],
				'xdebug.hashtype' => 'sha1',
				'xdebug.hash'     => $phpInfo['xdebug']['sha1'],
			]
		);
		$dockerTemplate->write($dockerPath);

		foreach ($this->configs as $config) {
			/*
			 * @todo: Prepare initial html content for each configuration in "{$this->dockyard}/docker/php-{$this->version}/html"
			 */
		}
	}
}
