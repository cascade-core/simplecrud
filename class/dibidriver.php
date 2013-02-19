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


class DibiDriver extends AbstractDriver
{
	protected $default_query_class = '\SimpleCrud\DibiQueryBuilder';

	private $table;
	private $dbinfo;


	public function __construct($prefix, $config)
	{
		parent::__construct($prefix, $config);

		$this->table = $this->config['db_table'];
		$this->dbinfo = \dibi::getDatabaseInfo();
	}


	public function getConfig($key = null)
	{
		if ($key === null) {
			return $this->config;
		} else {
			return $this->config[$key];
		}
	}


	public function describe()
	{
		if (!$this->dbinfo->hasTable($this->table)) {
			error_msg('Requested table "%s" does not exist!', $this->table);
			return false;
		}

		$info = $this->dbinfo->getTable($this->table);

		// Get properties
		$properties = array();
		foreach($info->getColumns() as $col) {
			$properties[$col->getName()] = array(
				'name' => $col->getName(),
				'type' => $col->getNativeType(),
				'size' => $col->getSize(),
				'default' => $col->getDefault(),
				'optional' => $col->isNullable(),
			);
		}

		// Get primary key
		$pkinfo = $info->getPrimaryKey();
		if ($pkinfo) {
			$pk = array();
			foreach ($pkinfo->getColumns() as $col) {
				$pk[] = $col->getName();
			}
		} else {
			$pk = null;
		}

		return array(
			'table' => $this->table,
			'primary_key' => $pk,
			'properties' => $properties,
		);
	}


	/**
	 * Create item
	 */
	public function createItem($item)
	{
		if (!\dibi::query('INSERT INTO ['.$this->table.']', $item)) {
			return false;
		}
		return \dibi::insertId();
	}

}

