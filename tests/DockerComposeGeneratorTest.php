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
		$info      = $generator->write('dockyard/docker-compose.yml');

		$this->assertFileContains(
			'dockyard/docker-compose.yml',
			[
				"build: docker/apache-5.4",
				"build: docker/php-latest",
				"image: 'nginx:1.8'",
				"image: 'nginx:1.9'",
				"image: 'mysql:latest'",
				"image: 'postgres:latest'",
			]
		);

		$expected = [
			'j25-mysqli.dev'       => [
				'name'   => 'apachev5p4',
				'url'    => 'j25-mysqli.dev',
				'volume' => getcwd() . "/dockyard/docker/apache-5.4/html/j25-mysqli.dev",
			],
			'j3-postgresql-19.dev' => [
				'name'   => 'nginxv1p9',
				'url'    => 'j3-postgresql-19.dev',
				'volume' => getcwd() . "/dockyard/docker/nginx-1.9/html/j3-postgresql-19.dev",
			],
			'j3-postgresql-18.dev' => [
				'name'   => 'nginxv1p8',
				'url'    => 'j3-postgresql-18.dev',
				'volume' => getcwd() . "/dockyard/docker/nginx-1.8/html/j3-postgresql-18.dev",
			],
			'j3-mysql.dev'         => [
				'name'   => 'nginxvlatest',
				'url'    => 'j3-mysql.dev',
				'volume' => getcwd() . "/dockyard/docker/nginx-latest/html/j3-mysql.dev",
			],
		];
		$this->assertEquals($expected, $info);
	}
}
