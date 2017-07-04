<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation;

use Joomla\Tests\Virtualisation\Service\ServiceTestCase;
use Joomla\Virtualisation\DockerComposeGeneratorApi;

class ApiGeneratorTest extends ServiceTestCase
{
	public function testTheApiGenerator()
	{
		$env = array(
			'php' => ['5.4', '5.5', '5.6', '7.0', '7.1'],
			'joomla' => ['3.6'],
			'selenium.no' => 3,
			'extension.path' => __DIR__ . '/../../weblinks',
		);
		(new DockerComposeGeneratorApi())->generateFromConfig($env);
	}
}
