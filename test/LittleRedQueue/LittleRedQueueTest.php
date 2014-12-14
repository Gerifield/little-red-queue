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

		$this->predis = $this->getMockBuilder('\Predis\Client');

		$this->object = new LittleRedQueue(
			$this->predis->getMock()
		);
	}

	/**
	 * @test
	 */
	public function createTest()
	{
		$this->assertInstanceOf('LittleRedQueue\LittleRedQueue', LittleRedQueue::create());
	}

	/**
	 * @test
	 */
	public function createWithConfigTest()
	{
		$this->assertInstanceOf('LittleRedQueue\LittleRedQueue', LittleRedQueue::createWithConfig());
	}
}
