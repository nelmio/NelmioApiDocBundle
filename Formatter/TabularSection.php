<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Formatter;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class TabularSection
{
	private $headers;
	private $data;
	private $title = "";
	private $type = "tabular";

	public function __construct(array $headers)
	{
		$this->headers = $headers;
	}

	public function addRow(array $data)
	{
		if (count($data) != count($this->headers)) {
			throw new \IllegalArgumentException("Wrong number of rows");
		}

		$this->data []= $data;
	}

	public function getType()
	{
		return $this->type;
	}


	/**
	 * @return array
	 */
	public function getHeaders() {
	    return $this->headers;
	}

	/**
	 * @param array
	 */
	public function setHeaders($headers)
	{
	    $this->headers = $headers;
	}

	public function getRows()
	{
		return $this->data;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
	    return $this->title;
	}

	/**
	 * @param string
	 */
	public function setTitle($title)
	{
	    $this->title = $title;
	}
}