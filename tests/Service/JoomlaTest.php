<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Service\Service;
use Joomla\Virtualisation\ServiceFactory;

class JoomlaTest extends ServiceTestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration((new ServerConfig)->loadFromFile(__DIR__ . '/../fixtures/j25.xml'));
		$this->service = $serverFactory->getApplication();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [];

		$result = $this->service->getSetup();

		$this->assertEquals($expected, $result);
	}

	public function testFilesystemIsSetUp()
	{
		$this->service->prepare();

		$this->assertFileContains(
			'dockyard/docker/apache-5.4/html/j25-mysqli.dev/libraries/cms/version/version.php',
			[
				'public $RELEASE = \'2.5\';',
				'public $DEV_LEVEL = \'28\';',
			]
		);
	}

	/**
	 * @depends testFilesystemIsSetUp
	 */
	public function testInstallationSqlIsGenerated()
	{
		$this->assertFileContains(
			'dockyard/mysql-latest/joomla25.sql',
			[
				'CREATE TABLE `j2m_content`',
			]
		);
	}

	/**
	 * @depends testFilesystemIsSetUp
	 */
	public function testSampleDataIsGenerated()
	{
		$this->assertFileContains(
			'dockyard/mysql-latest/joomla25.sql',
			[
				'(6, 102, \'Australian Parks \', \'australian-parks\', \'\', \'<p><img src="images/sampledata/parks/banner_cradle.jpg" border="0" alt="Cradle Park Banner" /></p>',
			]
		);
	}

	/**
	 * @depends testFilesystemIsSetUp
	 */
	public function testConfigurationFileIsGenerated()
	{
		$this->assertFileContains(
			'dockyard/docker/apache-5.4/html/j25-mysqli.dev/configuration.php',
			[
				'public $smtphost = \'mail:1025\';',
			]
		);
	}
}
