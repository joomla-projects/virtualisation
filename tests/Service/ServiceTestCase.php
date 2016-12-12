<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation\Service;

class ServiceTestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Assert that a file exists and contains the provided strings
	 *
	 * @param  string   $filename The filename
	 * @param  string[] $strings  The strings
	 */
	protected function assertFileContains($filename, $strings)
	{
		$this->assertFileExists($filename);

		$content = file_get_contents($filename);
		foreach ($strings as $string)
		{
			$this->assertContains($string, $content);
		}
	}
}
