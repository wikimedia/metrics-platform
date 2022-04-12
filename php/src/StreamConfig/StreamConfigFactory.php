<?php

namespace Wikimedia\Metrics\StreamConfig;

class StreamConfigFactory {

	/** @var array|false */
	protected $rawStreamConfigs;

	/**
	 * @param array|false $rawStreamConfigs
	 */
	public function __construct( $rawStreamConfigs ) {
		$this->rawStreamConfigs = $rawStreamConfigs;
	}

	/**
	 * Gets the configuration for the given stream.
	 *
	 * Note well that if the raw stream configuration is falsy, then this will always return
	 * `null`.
	 *
	 * @param string $streamName
	 * @return ?StreamConfig
	 * @throws StreamConfigException If the given stream is not configured
	 * @throws StreamConfigException If the given stream configuration is not an ordered dictionary
	 */
	public function getStreamConfig( string $streamName ): ?StreamConfig {
		if ( $this->rawStreamConfigs === false ) {
			return null;
		}

		if (
			!isset( $this->rawStreamConfigs[ $streamName ] )
			|| !is_array( $this->rawStreamConfigs[ $streamName ] )
		) {
			throw new StreamConfigException( 'The stream configuration is not defined or is not an array.' );
		}

		return new StreamConfig( $this->rawStreamConfigs[$streamName] );
	}
}