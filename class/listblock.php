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

class ListBlock extends \Block
{

	protected $inputs = array(
		'defaults' => null,	// Default values of filters.
		'*' => null,		// Filters (one per input). These override 'default' input when connected.
	);

	protected $outputs = array(
		'items' => true,	// Requested items
		'filters' => true,	// Used filters
		'total_count' => true,	// Total count of matching items (without limit)
		'done' => true,		// True, if count > 0
	);

	protected $driver;
	protected $prefix;
	protected $config;
	protected $query;


	/**
	 * Setup block to act as expected. Configuration is done by SimpleCrud 
	 * Block Storage.
	 */
	public function __construct($driver, $prefix, $config)
	{
		$this->driver = $driver;
		$this->prefix = $prefix;
		$this->config = $config;

		$this->query = $this->driver->prepareQuery();
		$this->inputs = $this->query->getDefaultFilters();
	}


	public function main()
	{
		// Collect filters
		$filters = (array) $this->in('defaults');
		foreach ($this->inputNames() as $input) {
			$filters[$input] = $this->in($input);
		}

		// Query items
		$this->query->addFilters($filters);
		$this->query->execute();

		// Get results
		$items = $this->query->getItems();
		$total_count = $this->query->getTotalCount();

		$this->out('items', $items);
		$this->out('filters', $filters);
		$this->out('total_count', $total_count);
		$this->out('done', count($items) > 0);
	}

}


