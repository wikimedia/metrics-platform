<?php

namespace Wikimedia\Metrics\Test;

use Generator;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Wikimedia\Metrics\StreamConfig\StreamConfigException;
use Wikimedia\Metrics\StreamConfig\ValidatingStreamConfigFactory;

/**
 * @coversDefaultClass \Wikimedia\Metrics\StreamConfig\ValidatingStreamConfigFactory
 */
class ValidatingStreamConfigFactoryTest extends TestCase {
	private function getFactory( $rawStreamConfigs ): ValidatingStreamConfigFactory {
		$validator = new Validator();
		$logger = new NullLogger();

		return new ValidatingStreamConfigFactory( $rawStreamConfigs, $validator, $logger );
	}

	/**
	 * @covers ::getStreamConfig
	 */
	public function testItShouldHandleSimpleInvalidStreamConfigs(): void {
		$this->assertNull( $this->getFactory( false )->getStreamConfig( 'test.stream' ) );
		$this->assertNull( $this->getFactory( [] )->getStreamConfig( 'test.stream' ) );
	}

	public function provideShouldHandleValidStreamConfigs(): Generator {
		yield [
			'streamConfig' => [],
			'expectedRequestValues' => [],
			'expectedCurationRules' => [],
		];
		yield [
			'streamConfig' => [
				'producers' => []
			],
			'expectedRequestValues' => [],
			'expectedCurationRules' => [],
		];
		yield [
			'streamConfig' => [
				'producers' => [
					'foo_producer' => [
						'bar' => 'baz',
					],
				],
			],
			'expectedRequestValues' => [],
			'expectedCurationRules' => [],
		];
		yield [
			'streamConfig' => [
				'producers' => [
					'metrics_platform_client' => []
				]
			],
			'expectedRequestValues' => [],
			'expectedCurationRules' => [],
		];
	}

	/**
	 * @dataProvider provideShouldHandleValidStreamConfigs
	 * @covers ::getStreamConfig
	 */
	public function testItShouldHandleValidStreamConfigs(
		$rawStreamConfig,
		$expectedRequestValues,
		$expectedCurationRules
	): void {
		$streamName = 'test.stream';
		$streamConfigs = [
			$streamName => $rawStreamConfig,
		];

		$factory = $this->getFactory( $streamConfigs );
		$streamConfig = $factory->getStreamConfig( $streamName );

		$this->assertNotNull( $streamConfig );
		$this->assertEquals( $expectedRequestValues, $streamConfig->getRequestedValues() );
		$this->assertEquals( $expectedCurationRules, $streamConfig->getCurationRules() );
	}

	public function provideMetricsPlatformClientConfig(): Generator {
		yield [
			'metricsPlatformClientConfig' => [
				'foo' => [],
			],
		];

		// provide_values
		yield [
			'metricsPlatformClientConfig' => [
				'provide_values' => [
					'foo',
				],
			],
		];
		yield [
			'metricsPlatformClientConfig' => [
				'provide_values' => [
					'page_id',
					'page_id',
				],
			],
		];

		// curation
		yield [
			'metricsPlatformClientConfig' => [
				'curation' => [],
			],
		];
		yield [
			'metricsPlatformClientConfig' => [
				'curation' => [
					// Valid contextual attribute name with valid operator and invalid operand.
					'performer_is_logged_in' => [
						'equals' => [],
					],
				],
			],
		];
		yield [
			'metricsPlatformClientConfig' => [
				'curation' => [
					// Invalid contextual attribute name with valid operator/operand.
					'performer_is_not_logged_in' => [
						'equals' => true,
					],
				],
			],
		];

		// NOTE: We do not make assertions about the validation of the "sample" property because
		// it does not make sense in this context.
	}

	/**
	 * @dataProvider provideMetricsPlatformClientConfig
	 * @covers ::getStreamConfig
	 */
	public function testItShouldValidateMetricsPlatformClientConfig( $metricsPlatformClientConfig ): void {
		$this->expectException( StreamConfigException::class );

		$streamName = 'test.stream';
		$rawStreamConfigs = [
			$streamName => [
				'producers' => [
					'metrics_platform_client' => $metricsPlatformClientConfig,
				],
			],
		];

		$this->getFactory( $rawStreamConfigs )
			->getStreamConfig( 'test.stream' );
	}
}