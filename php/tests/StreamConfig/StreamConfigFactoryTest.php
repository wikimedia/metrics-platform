<?php

namespace Wikimedia\Metrics\Tests\StreamConfig;

use Generator;
use PHPUnit\Framework\TestCase;
use Wikimedia\Metrics\StreamConfig\StreamConfig;
use Wikimedia\Metrics\StreamConfig\StreamConfigException;
use Wikimedia\Metrics\StreamConfig\StreamConfigFactory;

/**
 * @coversDefaultClass \Wikimedia\Metrics\StreamConfig\StreamConfigFactory
 */
class StreamConfigFactoryTest extends TestCase {
	private function getFactory( $rawStreamConfigs ): StreamConfigFactory {
		return new StreamConfigFactory( $rawStreamConfigs );
	}

	/**
	 * @covers ::getStreamConfig
	 */
	public function testGetStreamConfig(): void {
		$factory = $this->getFactory( false );

		$this->assertEquals( new StreamConfig( [] ), $factory->getStreamConfig( 'test.stream' ) );
		$this->assertEquals( new StreamConfig( [] ), $factory->getStreamConfig( 'foo' ) );

		// ---

		$factory = $this->getFactory( [
			'test.stream' => [],
		] );

		$this->assertEquals( new StreamConfig( [] ), $factory->getStreamConfig( 'test.stream' ) );
	}

	public function provideStreamConfigThrows(): Generator {
		yield [ false ];
		yield [ true ];
		yield [ 1 ];
		yield [ 'foo' ];
	}

	/**
	 * @covers ::getStreamConfig
	 * @dataProvider provideStreamConfigThrows
	 */
	public function testGetStreamConfigThrows( $rawStreamConfig ): void {
		$this->expectException( StreamConfigException::class );

		$factory = $this->getFactory( [
			'test.stream' => $rawStreamConfig,
		] );
		$factory->getStreamConfig( 'foo' );
	}

	/**
	 * @covers ::getStreamNamesForEvent
	 */
	public function testgetStreamNamesForEvent(): void {
		$factory = $this->getFactory( [
			'test.stream' => [],
			'test.stream2' => [
				'producers' => [
					'metrics_platform_client' => [
						'events' => [
							'foo',
							'bar',
						],
					],
				],
			],
			'test.stream3' => [
				'producers' => [
					'metrics_platform_client' => [
						'events' => [
							'bar',
						],
					],
				],
			],
		] );

		$this->assertEquals( [ 'test.stream2' ], $factory->getStreamNamesForEvent( 'foo' ) );
		$this->assertEquals( [ 'test.stream2', 'test.stream3' ], $factory->getStreamNamesForEvent( 'bar' ) );
		$this->assertEquals( [], $factory->getStreamNamesForEvent( 'baz' ) );

		$this->assertEquals(
			[ 'test.stream2', 'test.stream3' ],
			$factory->getStreamNamesForEvent( 'barBaz' )
		);

		// ---

		$factory = $this->getFactory( false );

		$this->assertEmpty( $factory->getStreamNamesForEvent( 'foo' ) );
	}
}
