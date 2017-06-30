<?php
/**
 * Created by PhpStorm.
 * User: isac
 * Date: 21/06/2017
 * Time: 5:34 PM
 */

namespace Joomla\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;

class SeleniumContainer extends AbstractService
{

	protected $no;

	public function __construct($version, ServerConfig $config)
	{
		parent::__construct($version, $config);

		// The Apache service uses the PHP version!
		$this->no = $this->configs[0]->get('selenium.no');
	}


	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name               = 'selenium-' . $this->no;
		$extensionPath      = $this->configs[0]->get("extension.path");
		$setup[$name] = [
			'image'   => 'joomlaprojects/joomla-testing-client-firefox:' . $this->version,
			'volumes' => [
				"$extensionPath:/usr/src/tests",
			],
		];
		$setup[$name]['networks'][]  = $this->network;

		return $setup;
	}

	public function prepare(){

	}
}