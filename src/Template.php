<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation;

/**
 * Class Template
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Template
{
	/**
	 * @var  string
	 */
	protected $path;

	/**
	 * @var  string[]
	 */
	protected $variables = [];

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function setVariables(array $vars)
	{
		$this->variables = $vars;
	}

	public function write($destination)
	{
		return $this->copy($this->path, $destination);
	}

	private function copy($src, $dst)
	{
		if (!file_exists(dirname($src)))
		{
			throw new \RuntimeException("Unable to process $src - does not exist");
		}

		if (is_file($src))
		{
			if (!file_exists(dirname($dst)))
			{
				mkdir(dirname($dst), 0777, true);
			}

			return file_put_contents($dst, $this->interpolate(file_get_contents($src)));
		}

		$len = 0;

		foreach (array_diff(scandir($src), ['.', '..']) as $file)
		{
			$len += $this->copy($src . '/' . $file, $dst . '/' . $file);
		}

		return $len;
	}

	private function interpolate($str)
	{
		foreach ($this->variables as $variable => $value)
		{
			$str = str_replace('${' . $variable . '}', $value, $str);
		}

		return $str;
	}
}
