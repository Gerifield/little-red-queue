<?php

namespace LittleRedQueue;

use Predis\Client;

/**
 * @see LittleRedQueue\Test\LittleRedQueueTest
 */
class LittleRedQueue {

	/**
	 * @var Client
	 */
	private $predis;

	public static function create()
	{
		return new self(
			new Client()
		);
	}

	public static function createWithConfig($host = '127.0.0.1', $port = 6379, $scheme = 'tcp')
	{
		return new self(
			new Client(array(
				'scheme' => $scheme,
				'host'   => $host,
				'port'   => $port,
			))
		);
	}

	public function __construct(Client $predis)
	{
		$this->predis = $predis;
	}
}
