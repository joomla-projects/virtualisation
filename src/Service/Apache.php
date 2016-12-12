<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Greencape\PhpVersions;
use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Template;

/**
 * Class Apache
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Apache extends AbstractService
{
	protected $setup = [];

	public function __construct($version, ServerConfig $config)
	{
		parent::__construct($version, $config);

		// The Apache service uses the PHP version!
		$this->version = $config->getVersion('php');
	}

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name               = 'apache-' . $this->version;
		$dockerPath         = $this->dockyard . '/docker/' . $name;
		$this->setup[$name] = [
			'build'   => $dockerPath,
			'volumes' => [
				"$dockerPath/conf:/etc/apache2/sites-enabled",
				"$dockerPath/html:/var/www/html",
				getcwd() . '/vendor:/usr/local/lib/php/vendor',
			],
			'links'   => [],
		];

		foreach ($this->configs as $config)
		{
			$driver                        = Map::getType($config->get('database.driver'));
			$version                       = $config->getVersion('database');
			$this->setup[$name]['links'][] = "$driver-$version";
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
		$name       = 'apache-' . $this->version;
		$dockerPath = $this->dockyard . '/docker/' . $name;

		$phpInfo        = (new PhpVersions())->getInfo($this->version);
		$dockerTemplate = new Template(__DIR__ . '/docker/apache');
		$dockerTemplate->setVariables(
			[
				'php.version'     => $this->version,
				'xdebug.version'  => $phpInfo['xdebug']['version'],
				'xdebug.hashtype' => 'sha1',
				'xdebug.hash'     => $phpInfo['xdebug']['sha1'],
			]
		);
		$dockerTemplate->write($dockerPath);

		$vhostTemplate = new Template(__DIR__ . '/template/apache/vhost.conf');

		foreach ($this->configs as $config)
		{
			$domain = $config->get('name') . '.' . $config->get('server.tld');
			$vhostTemplate->setVariables(
				[
					'domain' => $domain,
				]
			);
			$vhostTemplate->write("$dockerPath/conf/$domain");
			/*
			 * @todo: Prepare initial html content for each configuration in "{$this->dockyard}/apache-{$this->version}/html"
			 */
		}
	}
}
