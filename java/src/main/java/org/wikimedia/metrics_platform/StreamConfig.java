package org.wikimedia.metrics_platform;

import com.google.gson.annotations.SerializedName;

public class StreamConfig {

    @SerializedName("stream") private String streamName;

    @SerializedName("schema_title") private String schemaTitle;

    @SerializedName("destination_event_service") private DestinationEventService destinationEventService;

    @SerializedName("producers") private ProducerConfig producerConfig;

    /**
     * Constructor for testing.
     *
     * In practice, field values will be set by Gson during deserialization using reflection.
     *
     * @param streamName stream name
     * @param schemaTitle schema title
     * @param destinationEventService destination
     * @param producerConfig producer configuration
     */
    StreamConfig(
            String streamName,
            String schemaTitle,
            DestinationEventService destinationEventService,
            ProducerConfig producerConfig
    ) {
        this.streamName = streamName;
        this.schemaTitle = schemaTitle;
        this.destinationEventService = destinationEventService;
        this.producerConfig = producerConfig;
    }

    public boolean hasSamplingConfig() {
        return producerConfig != null &&
                producerConfig.metricsPlatformClientConfig != null &&
                producerConfig.metricsPlatformClientConfig.samplingConfig != null;
    }

    public ProducerConfig getProducerConfig() {
        return producerConfig;
    }

    String getStreamName() {
        return streamName;
    }

    String getSchemaTitle() {
        return schemaTitle;
    }

    DestinationEventService getDestinationEventService() {
        return destinationEventService != null ? destinationEventService : DestinationEventService.ANALYTICS;
    }

    public static class ProducerConfig {
        @SerializedName("metrics_platform_client") MetricsPlatformClientConfig metricsPlatformClientConfig;

        public ProducerConfig(MetricsPlatformClientConfig metricsPlatformClientConfig) {
            this.metricsPlatformClientConfig = metricsPlatformClientConfig;
        }

        public MetricsPlatformClientConfig getMetricsPlatformClientConfig() {
            return metricsPlatformClientConfig;
        }

        public static class MetricsPlatformClientConfig {
            @SerializedName("sampling") SamplingConfig samplingConfig;

            public MetricsPlatformClientConfig(SamplingConfig samplingConfig) {
                this.samplingConfig = samplingConfig;
            }

            public SamplingConfig getSamplingConfig() {
                return samplingConfig;
            }
        }
    }

}
