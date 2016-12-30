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
		$dockerPath         = 'docker/' . $name;
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
		$name       = 'apache-' . $this->version;
		$dockerPath = $this->dockyard . '/docker/' . $name;

		$phpVersions = new PhpVersions();
		$phpInfo     = $phpVersions->getInfo($this->version);

		$php = $phpVersions->getSourceInfo($phpInfo['version']);

		if ($phpInfo['museum'])
		{
			$major     = intval($phpInfo['version']);
			$phpUrl    = "http://museum.php.net/php{$major}/{$php['filename']}";
			$phpAscUrl = '';
		}
		else
		{
			$phpUrl    = "https://secure.php.net/get/{$php['filename']}/from/this/mirror";
			$phpAscUrl = "https://secure.php.net/get/{$php['filename']}.asc/from/this/mirror";
		}

		$gpgKeys = [];
		foreach ($phpInfo['gpg'] as $key)
		{
			$gpgKeys[] = str_replace(' ', '', $key['pub']);
		}

		$dockerTemplate = new Template(__DIR__ . '/docker/apache');
		$dockerTemplate->setVariables(
			[
				'php.filename'    => $php['filename'],
				'php.version'     => $phpInfo['version'],
				'php.url'         => $phpUrl,
				'php.asc.url'     => $phpAscUrl,
				'php.md5'         => $php['md5'],
				'php.sha256'      => $php['sha256'],
				'gpg.keys'        => implode(' ', $gpgKeys),
				'xdebug.version'  => $phpInfo['xdebug']['version'],
				'xdebug.hashtype' => 'sha1',
				'xdebug.hash'     => $phpInfo['xdebug']['sha1'],
			]
		);
		$dockerTemplate->write($dockerPath);

		$vhostTemplate = new Template(__DIR__ . '/template/apache/vhost.conf');

		foreach ($this->configs as $config)
		{
			$domain = $config->getDomain();
			$vhostTemplate->setVariables(
				[
					'domain' => $domain,
				]
			);
			$vhostTemplate->write("$dockerPath/conf/$domain");
		}
	}
}
