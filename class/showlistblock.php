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

class ShowListBlock extends \Block
{

	protected $inputs = array(
		'items' => null,		// Items to show
		'preset' => 'list',		// Preset used to format items (as specified in configuration)
		'slot' => 'default',
		'slot_weight' => 50,
	);

	protected $outputs = array(
		'done' => true,
	);

	const force_exec = true;


	protected $driver;
	protected $prefix;
	protected $config;


	/**
	 * Setup block to act as expected. Configuration is done by SimpleCrud
	 * Block Storage.
	 */
	public function __construct($driver, $prefix, $config)
	{
		$this->driver = $driver;
		$this->prefix = $prefix;
		$this->config = $config;
	}


	public function main()
	{
		$items = $this->in('items');
		$preset = $this->in('preset');

		// Stop if there is nothing to show.
		if (empty($items)) {
			return;
		}

		// Get view configuration
		$view_cfg = $this->calculate_view_config($preset);
		$view_cfg['items'] = $items;

		$this->template_add(null, $view_cfg['template'], $view_cfg);

                $this->out('done', true);
	}


	protected function calculate_view_config($preset)
	{
		if (!array_key_exists($preset, $this->config['views'])) {
			error_msg("View preset \"%s\" is not defined for prefix \"%s\".", $preset, $this->prefix);
			return;
		}

		$view_cfg = $this->config['views'][$preset];

		$used_presets = array(
			$preset => true,
		);

		// Resolve inheritance
		while (!empty($view_cfg['extends'])) {
			$extends = $view_cfg['extends'];
			unset($view_cfg['extends']);

			if (!array_key_exists($extends, $this->config['views'])) {
				error_msg("Parent preset \"%s\" is not defined for prefix \"%s\".", $preset, $this->prefix);
				return;
			}

			if (isset($used_presets[$extends])) {
				error_msg('Cyclic inheritance detected while composing preset "%s" (circle closes at "%s") for prefix "%s".',
					$preset, $extends, $this->prefix);
				return false;
			}

			$parent_cfg = $this->config['views'][$extends];
			$used_presets[$extends] = true;

			$view_cfg = array_merge_recursive($parent_cfg, $view_cfg);
		}

		// Sort fields by weight
		if (is_array($view_cfg['fields'])) {
			uasort($view_cfg['fields'], function($a, $b) {
					return (isset($a['weight']) ? $a['weight'] : 50) - (isset($b['weight']) ? $b['weight'] : 50);
				});
		}

		return $view_cfg;
	}

}

