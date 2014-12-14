<?php

namespace LittleRedQueue\Test;

use LittleRedQueue\LittleRedQueue;

class LittleRedQueueTest extends \PHPUnit_Framework_TestCase
{
	const TEST_KEY   = 'key1';
	const TEST_VALUE = 'theStringValue1';

	/**
	 * @var LittleRedQueue
	 */
	private $object;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $predis;

	protected function setUp()
	{
		parent::setUp();

		$this->predis = $this->getMockBuilder('\Predis\Client')
			->disableOriginalConstructor()
			->setMethods(array(
				'connect', 'isConnected', 'blpop', 'rpush'
			)) //because blpop, rpush etc. are magic methods!
			->getMock();

		$this->object = new LittleRedQueue(
			$this->predis
		);
	}

	/**
	 * @test
	 */
	public function testCreate()
	{
		$this->assertInstanceOf('LittleRedQueue\LittleRedQueue', LittleRedQueue::create());
	}

	/**
	 * @test
	 */
	public function testCreateWithConfig()
	{
		$this->assertInstanceOf('LittleRedQueue\LittleRedQueue', LittleRedQueue::createWithConfig());
	}

	public function testCheckConnectionAlreadyConnected()
	{
		$this->predisConnect();

		$this->assertTrue($this->object->checkConnection());
	}

	/**
	 * @test
	 */
	public function testCheckConnectionNewConnection()
	{
		$this->predis->expects($this->once())
			->method('isConnected')
			->willReturn(false);

		$this->predis->expects($this->once())
			->method('connect');

		$this->assertTrue($this->object->checkConnection());
	}

	/**
	 * @test
	 */
	public function testCheckConnectionNewConnectionError()
	{
		$this->predis->expects($this->once())
			->method('isConnected')
			->willReturn(false);

		$exception = $this->getMockBuilder('\Predis\Connection\ConnectionException')
			->disableOriginalConstructor()
			->getMock();

		$this->predis->expects($this->once())
			->method('connect')
			->willThrowException($exception);

		$this->assertFalse($this->object->checkConnection());
	}

	/**
	 * @test
	 */
	public function testGetSuccess()
	{
		$this->predisConnect();

		$this->predis->expects($this->once())
			->method('blpop')
			->willReturn(
				array(
					self::TEST_KEY,
					self::TEST_VALUE
				)
			);

		$this->assertEquals(
			self::TEST_VALUE,
			$this->object->get(self::TEST_KEY)
		);
	}

	/**
	 * @test
	 */
	public function testGetTimeout()
	{
		$this->predisConnect();

		$this->predis->expects($this->once())
			->method('blpop')
			->willReturn(null);

		$this->assertEquals(null, $this->object->get(self::TEST_KEY));
	}

	/**
	 * @test
	 */
	public function testGetNoConnection()
	{
		$this->predisConnect(false);

		$this->assertEquals(null, $this->object->get(self::TEST_KEY));
	}

	/**
	 * @test
	 */
	public function testPutSuccess()
	{
		$this->predisConnect();

		$this->predis->expects($this->once())
			->method('rpush')
			->willReturn(true);

		$this->assertTrue($this->object->put(self::TEST_KEY, self::TEST_VALUE));
	}

	/**
	 * @test
	 */
	public function testPutNoConnection()
	{
		$this->predisConnect(false);

		$this->assertEquals(false, $this->object->put(self::TEST_KEY, self::TEST_VALUE));
	}

	private function predisConnect($state = true, $numOfCalls = 1)
	{
		$this->predis->expects($this->exactly($numOfCalls))
			->method('isConnected')
			->willReturn($state);

		if (!$state) {
			$exception = $this->getMockBuilder('\Predis\Connection\ConnectionException')
				->disableOriginalConstructor()
				->getMock();

			$this->predis->expects($this->once())
				->method('connect')
				->willThrowException($exception);
		}
	}
}
