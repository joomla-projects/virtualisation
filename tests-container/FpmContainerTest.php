<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation\Container;

use PHPUnit\Framework\TestCase;

class FpmContainerTest extends TestCase
{
	private $rootDir;

	private $workDir;

	public function setUp()
	{
		$this->rootDir = dirname(__DIR__);
		$this->workDir = __DIR__ . '/docker-fpm';

		`rm -rf $this->workDir && mkdir -p $this->workDir && cp -R $this->rootDir/src/Service/docker/php-fpm/* $this->workDir`;
	}

	public function tearDown()
	{
		`docker rmi test_image`;
		`rm -rf $this->workDir`;
	}

	public function containerdata()
	{
		return [
			#'5.2' => ['5.2', '2.2.7'],
			#'5.3' => ['5.3', '2.2.7'],
			'5.4' => ['5.4', '2.4.1'],
			'5.5' => ['5.5', '2.5.3'],
			'5.6' => ['5.6', '2.5.3'],
			'7.0' => ['7.0', '2.5.3'],
			'7.1' => ['7.1', '2.5.3'],
		];
	}

	/**
	 * @dataProvider containerData
	 */
	public function testContainer($phpVersion, $xdebugVersion)
	{
		$replacements = [
			'${php.version}'    => $phpVersion,
			'${php.major}'      => intval($phpVersion),
			'${xdebug.version}' => $xdebugVersion,
		];
		$dockerFile   = $this->workDir . '/Dockerfile';
		file_put_contents(
			$dockerFile,
			str_replace(
				array_keys($replacements),
				array_values($replacements),
				file_get_contents($dockerFile)
			)
		);

		$result = `docker build --rm -t test_image $this->workDir`;
		$this->assertContains(
			'Successfully built',
			$result,
			"Build for $phpVersion failed."
		);

		$expectedStrings = [
			'PHP Version => ' . $phpVersion,
			'Server API => Command Line Interface',
			'with Xdebug v' . $xdebugVersion,
			'sendmail_path => /usr/local/bin/catchmail',
			'OpenSSL support => enabled',
			'MysqlI Support => enabled',
			'PostgreSQL Support => enabled',
			'PDO support => enabled',
			'PDO Driver for MySQL => enabled',
			'PDO Driver for PostgreSQL => enabled',
			'xdebug support => enabled',
		];

		$output = `docker run -it --rm test_image php -i`;

		foreach ($expectedStrings as $expected)
		{
			$this->assertContains(
				$expected,
				$output,
				"Command Line Interface output does not contain $expected"
			);
		}

		$expectedStrings = [
			'PHP Version => ' . $phpVersion,
			'Server API => FPM/FastCGI',
			'with Xdebug v' . $xdebugVersion,
			'sendmail_path => /usr/local/bin/catchmail',
			'OpenSSL support => enabled',
			'MysqlI Support => enabled',
			'PostgreSQL Support => enabled',
			'PDO support => enabled',
			'PDO Driver for MySQL => enabled',
			'PDO Driver for PostgreSQL => enabled',
			'xdebug support => enabled',
		];

		$output = `docker run -it --rm test_image php-fpm -i`;

		foreach ($expectedStrings as $expected)
		{
			$this->assertContains(
				$expected,
				$output,
				"FPM/FastCGI output does not contain $expected"
			);
		}
	}
}
