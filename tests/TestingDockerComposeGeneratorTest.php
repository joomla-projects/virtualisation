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

class TestingDockerComposeGeneratorTest extends ServiceTestCase
{
	public function testTheGeneratedFileContainsCorrectBuildInstructions()
	{
		$generator = new DockerComposeGenerator('tests/testing_environment');
		$generator->write('dockyard_testing/docker-compose.yml');
	}
}
