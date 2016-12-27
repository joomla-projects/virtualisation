<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation;

/**
 * Class ServerConfig
 *
 * Reads the configuration XML files and provides the corresponding values.
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class ServerConfig
{
	/**
	 * @var  string[]  Configuration values
	 */
	private $config = [
		'cache.enabled'       => "0",
		'cache.handler'       => "file",
		'cache.time'          => "15",
		'database.driver'     => "mysqli",
		'database.name'       => "joomla3",
		'database.prefix'     => "j3m_",
		'debug.language'      => "1",
		'debug.system'        => "1",
		'feeds.email'         => "author",
		'feeds.limit'         => "10",
		'host.dockyard'       => 'shipyard',
		'joomla.sampleData'   => "data",
		'joomla.version'      => "latest",
		'meta.description'    => "Test installation",
		'meta.keywords'       => "",
		'meta.showAuthor'     => "1",
		'meta.showTitle'      => "1",
		'meta.showVersion'    => "0",
		'mysql.name'          => "joomla_test",
		'mysql.password'      => "sqladmin",
		'mysql.rootPassword'  => "root",
		'mysql.user'          => "sqladmin",
		'mysql.version'       => "latest",
		'name'                => "default",
		'php.version'         => "latest",
		'postgresql.name'     => "joomla_test",
		'postgresql.password' => "sqladmin",
		'postgresql.user'     => "sqladmin",
		'postgresql.version'  => "latest",
		'sef.enabled'         => "0",
		'sef.rewrite'         => "0",
		'sef.suffix'          => "0",
		'sef.unicode'         => "0",
		'server.offset'       => "UTC",
		'server.tld'          => "dev",
		'server.type'         => "nginx",
		'session.handler'     => "database",
		'session.lifetime'    => "15",
	];

	/**
	 * ServerConfig constructor.
	 *
	 * @param   string $filename The path to the configuration file.
	 */
	public function __construct($filename)
	{
		$path = dirname($filename);

		$this->config = array_merge($this->config, $this->read($path . '/default.xml'));
		$this->config = array_merge($this->config, $this->read($path . '/database.xml'));
		$this->config = array_merge($this->config, $this->read($filename));
	}

	/**
	 * Read the configuration
	 *
	 * @param   string $filename The path to the configuration file.
	 *
	 * @return  string[]
	 */
	private function read($filename)
	{
		$config = [];

		$xml = simplexml_load_file($filename);

		foreach ($xml->attributes() as $key => $attribute)
		{
			$config[$key] = (string) $attribute;
		}

		/** @var \SimpleXMLElement $child */
		foreach ($xml->children() as $child)
		{
			$prefix = $child->getName();

			foreach ($child->attributes() as $key => $attribute)
			{
				$config["$prefix.$key"] = (string) $attribute;
			}
		}

		return $config;
	}

	public function getVersion($key)
	{
		$version = $this->get("$key.version");

		if (empty($version))
		{
			$version = 'latest';
		}

		return $version;
	}

	public function getDomain()
	{
		return $this->get('name') . '.' . $this->get('server.tld');
	}

	/**
	 * Get a configuration value
	 *
	 * @param   string $key The key
	 *
	 * @return  string  The value
	 */
	public function get($key)
	{
		if (!isset($this->config[$key]))
		{
			return null;
		}

		return $this->config[$key];
	}
}
