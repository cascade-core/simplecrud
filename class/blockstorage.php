<?php
/*
 * Copyright (c) 2012, Josef Kufner  <jk@frozen-doe.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the author nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

namespace SimpleCrud;

class BlockStorage implements \IBlockStorage
{
	private $config;
	private $drivers = array();

	private $block_classes = array(
			'describe'    => '\\SimpleCrud\\DescribeBlock',
			'read'        => '\\SimpleCrud\\ReadBlock',
			'list'        => '\\SimpleCrud\\ListBlock',
			'create'      => '\\SimpleCrud\\CreateBlock',
			'form_config' => '\\SimpleCrud\\FormConfigBlock',
			'show_table'  => '\\SimpleCrud\\ShowTableBlock',
			'show_list'   => '\\SimpleCrud\\ShowListBlock',
			'show_item'   => '\\SimpleCrud\\ShowItemBlock',
	);

	/**
	 * Constructor will get options from core.ini.php file.
	 */
	public function __construct($storage_opts)
	{
		$this->config = json_decode(file_get_contents($storage_opts), TRUE);

		if ($this->config === null) {
			$err_code = json_last_error();
			switch ($err_code) {
				case JSON_ERROR_NONE:           $err = 'No error has occurred.'; break;
				case JSON_ERROR_DEPTH:          $err = 'The maximum stack depth has been exceeded.'; break;
				case JSON_ERROR_STATE_MISMATCH: $err = 'Invalid or malformed JSON.'; break;
				case JSON_ERROR_CTRL_CHAR:      $err = 'Control character error, possibly incorrectly encoded.'; break;
				case JSON_ERROR_SYNTAX:         $err = 'Syntax error.'; break;
				case JSON_ERROR_UTF8:           $err = 'Malformed UTF-8 characters, possibly incorrectly encoded.'; break;
				default:                        $err = "#".$err_code; break;
			}
			error_msg("Failed to load config file \"%s\": %s", $storage_opts, $err);
		}
	}


	/**
	 * Returns current configuration.
	 */
	public function getConfiguration()
	{
		return $this->config;
	}


	/**
	 * Returns true if there is no way that this storage can modify or 
	 * create blocks. When creating or modifying block, first storage that 
	 * returns true will be used.
	 */
	public function isReadOnly()
	{
		return true;
	}


	/**
	 * Create instance of requested block and give it loaded configuration. 
	 * No further initialisation here, that is job for cascade controller. 
	 * Returns created instance or false.
	 */
	public function createBlockInstance($block)
	{
		$prefix = dirname($block);

		// Check if block exists
		if (!isset($this->config['entities'][$prefix])) {
			return false;
		}
		$cfg = $this->config['entities'][$prefix];

		// Prepare driver
		if (isset($this->drivers[$prefix])) {
			$driver = $this->drivers[$prefix];
		} else {
			$driver = new $cfg['driver_class']($prefix, $cfg);
			$this->drivers[$prefix] = $driver;
		}

		// Create requested block
		$block_name = basename($block);
		if (array_key_exists($block_name, $this->block_classes)) {
			return new $this->block_classes[$block_name]($driver, $prefix, $cfg);
		} else {
			return false;
		}
	}


	/**
	 * Load block configuration. Returns false if block is not found.
	 */
	public function loadBlock($block)
	{
		return isset($this->config['entities'][dirname($block)]);
	}


	/**
	 * Store block configuration.
	 */
	public function storeBlock($block, $config)
	{
		// This is read-only storage.
		return false;
	}


	/**
	 * Delete block configuration.
	 */
	public function deleteBlock($block)
	{
		// This is read-only storage.
		return false;
	}


	/**
	 * Get time (unix timestamp) of last modification of the block.
	 */
	public function blockMTime($block)
	{
		// All blocks are generated on demand = no mtime.
		return 0;
	}


	/**
	 * List all available blocks in this storage.
	 */
	public function getKnownBlocks(& $blocks = array())
	{
		foreach ($this->config['entities'] as $prefix => $cfg) {
			$plugin = preg_replace('/\/.*/', '', $prefix);
			foreach ($this->block_classes as $b => $c) {
				$blocks[$plugin][] = $prefix.'/'.$b;
			}
		}
	}

}

