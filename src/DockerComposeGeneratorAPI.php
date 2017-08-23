<?php
/**
 * Created by PhpStorm.
 * User: isac
 * Date: 04/07/2017
 * Time: 9:45 AM
 */

namespace Joomla\Virtualisation;

class DockerComposeGeneratorAPI extends DockerComposeGenerator
{
	public function __construct()
	{
		parent::__construct(__DIR__ . '/../tests/fixtures_parallel_testing');
	}
}
