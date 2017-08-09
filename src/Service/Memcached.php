<?php
/**
 * Created by PhpStorm.
 * User: isac
 * Date: 09/08/2017
 * Time: 5:03 PM
 */

namespace Joomla\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;

class Memcached extends AbstractService
{

	public function __construct($version, ServerConfig $config)
	{
		parent::__construct($version, $config);
	}


	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name = 'memcached';
		$config = $this->configs[0];
		$port = $config->get('memcached.port');

		$setup[$name] = [
			'image'   => 'memcached:' . $this->version,
			'ports'   => ["$port:11211"],
		];
		$setup[$name]['networks'][]  = $this->network;



		return $setup;
	}

	public function prepare(){

	}
}