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

class DockerComposeGeneratorTest extends ServiceTestCase
{
	public function testTheGeneratedFileContainsCorrectBuildInstructions()
	{
		$generator = new DockerComposeGenerator('tests/fixtures');
		$generator->write('dockyard/docker-compose.yml');

		$this->assertFileContains(
			'dockyard/docker-compose.yml',
			[
				"build: dockyard/docker/apache-5.2",
				"build: dockyard/docker/php-5.2",
				"build: dockyard/docker/php-latest",
				"image: 'nginx:1.8'",
				"image: 'nginx:1.9'",
				"image: 'greencape/mariadb:latest'",
				"image: 'postgres:latest'",
			]
		);
	}
}
