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

/**
 * List all known SimpleCrud entities in way core/out/menu understands.
 */
class B_simplecrud__build_menu extends Block
{
	protected $inputs = array(
		'link' => '/{prefix}',	// Link in generated menu. Use any keys found in block storage config file.
	);

	protected $outputs = array(
		'items' => true,	// Menu for core/out/menu block.
		'done' => true,
	);

	public function main()
	{
		$link = $this->in('link');
		$items = array();

		foreach ($this->get_cascade_controller()->get_block_storages() as $storage) {
			if ($storage instanceof \SimpleCrud\BlockStorage) {
				foreach ($storage->get_configuration() as $prefix => $cfg) {
					$items[] = array(
						'title' => $cfg['name'],
						'link' => str_replace('_', '-', filename_format($link, array_merge($cfg, array('prefix' => $prefix)))),
					);
				}
			}
		}
		
		$this->out('items', $items);
		$this->out('done', true);
	}
}


