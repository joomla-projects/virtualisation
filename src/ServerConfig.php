<?php
/**
 * Part of the Joomla Testing Framework Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Testing;

/**
 * Class ServerConfig
 *
 * Reads the configuration XML files and provides the corresponding values.
 *
 * @package  Joomla\Testing
 * @since    __DEPLOY_VERSION__
 */
class ServerConfig
{
	/**
	 * @var  string[]  Configuration values
	 */
	private $config;

	/**
	 * ServerConfig constructor.
	 *
	 * @param   string  $filename  The path to the configuration file.
	 */
	public function __construct($filename)
	{
		$path = dirname($filename);

		$this->config = $this->read($path . '/default.xml');
		$this->config = array_merge($this->config, $this->read($path . '/database.xml'));
		$this->config = array_merge($this->config, $this->read($filename));
	}

	/**
	 * Get a configuration value
	 *
	 * @param   string $key  The key
	 *
	 * @return  string  The value
	 */
	public function get($key)
	{
		return isset($this->config[$key]) ? $this->config[$key] : null;
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

			foreach ($child->attributes() as $key => $attribute) {
				$config["$prefix.$key"] = (string) $attribute;
			}
		}

		return $config;
	}
}
