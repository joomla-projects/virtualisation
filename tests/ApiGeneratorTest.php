<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation;

use Joomla\Tests\Virtualisation\Service\ServiceTestCase;
use Joomla\Virtualisation\DockerComposeGenerator;

class ApiGeneratorTest extends ServiceTestCase
{
	public function testTheApiGenerator()
	{
		$env = array(
			'php' => ['5.4'],
			'joomla' => ['3.7', '3.8-dev', 'staging'],
			'selenium.no' => 3,
			'extension.path' => __DIR__ . '/../../weblinks',
		);
		$generator = new DockerComposeGenerator('tests/fixtures_parallel_testing');
		$generator->generateFromConfig($env);

	}
}
