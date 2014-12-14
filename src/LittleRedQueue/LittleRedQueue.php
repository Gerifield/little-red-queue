<?php

namespace LittleRedQueue;

use Predis\Client;
use Predis\Connection\ConnectionException;

/**
 * @see LittleRedQueue\Test\LittleRedQueueTest
 */
class LittleRedQueue {

	const QUEUE_PREFIX    = 'queue';
	const PRIORITY_HIGH   = 'high';
	const PRIORITY_NORMAL = 'normal';
	const PRIORITY_LOW    = 'low';

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

	/**
	 * @return bool
	 */
	public function checkConnection()
	{
		if (!$this->predis->isConnected()) {
			try {
				$this->predis->connect();
				return true;
			} catch (ConnectionException $e) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Get a job from queue.
	 * This call is blocking.
	 *
	 * @param string $key
	 * @param int    $timeout
	 *
	 * @return string|null
	 */
	public function get($key, $timeout = 30)
	{
		if ($this->checkConnection()) {

			$return = $this->predis->blpop(array(
					self::QUEUE_PREFIX . ':' . self::PRIORITY_HIGH . ':' . $key,
					self::QUEUE_PREFIX . ':' . self::PRIORITY_NORMAL . ':' . $key,
					self::QUEUE_PREFIX . ':' . self::PRIORITY_LOW . ':' . $key
				),
				$timeout
			);

			if (is_array($return)) {
				return $return[1];
			}
		}

		return null;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param string $priority
	 * @return bool
	 */
	public function put($key, $value, $priority = self::PRIORITY_NORMAL)
	{
		if ($this->checkConnection()) {
			return $this->predis->rpush(
				self::QUEUE_PREFIX . ':' . $priority . ':' . $key,
				$value
			);
		}
		return false;
	}
}
