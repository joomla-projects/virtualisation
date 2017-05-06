<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Greencape\PhpVersions;
use Joomla\Virtualisation\Template;

/**
 * Class PhpBase
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
abstract class PhpBase extends AbstractService
{
	protected $setup = [];

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	protected function preparePhp($dockerPath)
	{
		$phpVersions = new PhpVersions();
		$phpInfo     = $phpVersions->getInfo($this->version);

		$php = $phpVersions->getSourceInfo($phpInfo['version']);

		if ($phpInfo['museum'])
		{
			$major     = intval($phpInfo['version']);
			$phpUrl    = "http://museum.php.net/php{$major}/{$php['filename']}";
			$phpAscUrl = '';
		}
		else
		{
			$phpUrl    = "https://secure.php.net/get/{$php['filename']}/from/this/mirror";
			$phpAscUrl = "https://secure.php.net/get/{$php['filename']}.asc/from/this/mirror";
		}

		$dockerTemplate = new Template(__DIR__ . '/docker/apache');
		$dockerTemplate->setVariables(
			[
				'php.filename'    => $php['filename'],
				'php.version'     => $phpInfo['version'],
				'php.url'         => $phpUrl,
				'php.asc.url'     => $phpAscUrl,
				'php.md5'         => $php['md5'],
				'php.sha256'      => $php['sha256'],
				#'php.patches'     => implode(' ', $this->findPatches($dockerPath, $phpInfo['version'])),
				'gpg.keys'        => $this->getGpgKeys($phpInfo['gpg']),
				'xdebug.version'  => $phpInfo['xdebug']['version'],
				'xdebug.hashtype' => 'sha1',
				'xdebug.hash'     => $phpInfo['xdebug']['sha1'],
			]
		);
		$dockerTemplate->write($dockerPath);

		$vhostTemplate = new Template(__DIR__ . '/template/apache/vhost.conf');

		foreach ($this->configs as $config)
		{
			$domain = $config->getDomain();
			$vhostTemplate->setVariables(
				[
					'domain' => $domain,
				]
			);
			$vhostTemplate->write("$dockerPath/conf/$domain");
		}
	}

	/**
	 * @param $gpg
	 *
	 * @return array
	 */
	private function getGpgKeys($gpg)
	{
		$gpgKeys = [];

		foreach ($gpg as $key)
		{
			$gpgKeys[] = str_replace(' ', '', $key['pub']);
		}

		if (empty($gpgKeys))
		{
			$gpgKeys = ['""'];
		}

		return implode(' ', $gpgKeys);
	}

	protected function findPatches($path, $version)
	{
		$files = [];

		foreach (glob("$path/patches/*") as $filename)
		{
			if (preg_match("/^(.*?)-(.*?).patch$/", $filename, $current))
			{
				if (version_compare($current[1], $version, '<='))
				{
					if (isset($files[$current[2]]))
					{
						preg_match("/(.*?)-(.*?).patch/", $files[$current[2]], $best);
						if (version_compare($current[1], $best[1], '<='))
						{
							continue;
						}
					}

					$files[$current[2]] = basename($filename);
				}
			}
		}

		return $files;
	}
}
