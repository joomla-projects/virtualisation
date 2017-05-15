<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation\Container;

use PHPUnit\Framework\TestCase;

class ApacheContainerTest extends TestCase
{
	private $rootDir;

	private $workDir;

	public function setUp()
	{
		$this->rootDir = dirname(__DIR__);
		$this->workDir = __DIR__ . '/docker-apache';

		`rm -rf $this->workDir && mkdir -p $this->workDir && cp -R $this->rootDir/src/Service/docker/apache/* $this->workDir`;
	}

	public function tearDown()
	{
		`docker rm -f test_container && docker rmi test_image`;
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

		`docker run -d --name test_container test_image`;

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
			'PHP Version ' . $phpVersion,
			'Server API Apache 2.0 Handler',
			'with Xdebug v' . $xdebugVersion,
			'sendmail_path' .'/usr/local/bin/catchmail',
			'OpenSSL support enabled',
			'MysqlI Support' . 'enabled',
			'PostgreSQL Support' . 'enabled',
			'PDO support' . 'enabled',
			'PDO Driver for MySQL' . 'enabled',
			'PDO Driver for PostgreSQL' . 'enabled',
			'xdebug support' . 'enabled',
		];

		$info = json_decode(`docker inspect test_container`);
		$ip   = $info[0]->NetworkSettings->IPAddress;
		$this->assertRegExp('~\d+\.\d+\.\d+\.\d+~', $ip, "Could not get a valid IP address, got '$ip'");

		$index = __DIR__ . '/fixtures/index.php';
		`docker cp $index test_container:/var/www/html/index.php`;
		try {
			$output = file_get_contents("http://$ip/index.php");
		} catch (\Throwable $e) {
			`docker logs --details test_container`;
		}

		foreach ($expectedStrings as $expected)
		{
			$this->assertContains(
				$expected,
				$output,
				"Apache 2.0 Handler output does not contain $expected"
			);
		}
	}
}
