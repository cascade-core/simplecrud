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

class DibiQueryBuilder implements IQueryBuilder
{
	private $query;
	private $result;
	private $total_count;
	private $desc;

	public function __construct(AbstractDriver $driver)
	{
		$this->desc = $driver->describe();

		$this->query = \dibi::select('*');
		$this->query->setFlag('SQL_CALC_FOUND_ROWS');
		$this->query->from($driver->get_config('db_table'));

		// Default limit (filters can override this)
		$this->query->limit(50);
	}


	public function add_filters($filters)
	{
		// FIXME: This is very stupid approach, use methods to allow inheritance
		// It would be also better to implement this logic in abstract class.
		foreach ($filters as $filter => $value) {
			if ($value === null) {
				continue;
			}

			switch ($filter) {
				case 'order':
					if (array_key_exists($value, $this->desc['properties'])) {
						$this->query->orderBy('`'.$value.'` '.(empty($filters['reverse']) ? 'ASC' : 'DESC'));
					}
					break;

				case 'count':
					$this->query->limit((int) $value);
					break;
			}
		}
	}


	public function get_default_filters()
	{
		// TODO: When filters are implemented using methods, use reflection to enumerate them
		return array(
			'order' => null,
			'reverse' => false,
			'count' => 50,
		);
	}


	public function execute()
	{
		$this->result = $this->query->execute();
		$this->total_count = \dibi::query('SELECT FOUND_ROWS()')->fetchSingle();
	}


	public function get_items()
	{
		return $this->result->getIterator();
	}


	public function get_single_item()
	{
		$item = $this->result->fetch();
		$this->result->free();
		return $item;
	}


	public function get_total_count()
	{
		return $this->total_count;
	}
}

