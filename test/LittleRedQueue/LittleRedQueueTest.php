<?php

namespace LittleRedQueue\Test;

use LittleRedQueue\LittleRedQueue;

class LittleRedQueueTest extends \PHPUnit_Framework_TestCase
{
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
		$this->predis->expects($this->once())
			->method('isConnected')
			->willReturn(true);

		$this->assertTrue($this->object->checkConnection());
	}

	public function testCheckConnectionNewConnection()
	{
		$this->predis->expects($this->once())
			->method('isConnected')
			->willReturn(false);

		$this->predis->expects($this->once())
			->method('connect')
			->willReturn(true);

		$this->assertTrue($this->object->checkConnection());
	}

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
}
