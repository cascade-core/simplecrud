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

class ReadBlock extends \Block
{

	protected $inputs = array(
		'id' => null,
	);

	protected $outputs = array(
		'item' => true,		// Requested item
		'done' => true,		// True, if found
	);

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

		$desc = $this->driver->describe();

		// Use primary keys as input
		$this->inputs = array_fill_keys($desc['primary_key'], null);

		// Prepare outputs for properties
		$this->outputs = array_fill_keys(array_keys($desc['properties']), true);
		$this->outputs['item'] = true;
		$this->outputs['done'] = true;
	}


	public function main()
	{
		// Collect filters
		$filters = (array) $this->in('defaults');
		foreach ($this->input_names() as $input) {
			$filters[$input] = $this->in($input);
		}

		// Query items
		$query = $this->driver->prepare_query();
		$query->add_filters($filters);
		$query->execute();

		// Get results
		$item = $query->get_single_item();

		if ($item) {
			$this->out_all((array) $item);
			$this->out('item', $item);
			$this->out('done', true);
		}
	}

}

