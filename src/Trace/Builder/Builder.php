<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Builder;

use Exception;
use GR\Telephponic\Trace\Integration\Curl;
use GR\Telephponic\Trace\Integration\Grpc;
use GR\Telephponic\Trace\Integration\Integration;
use GR\Telephponic\Trace\Integration\Memcached;
use GR\Telephponic\Trace\Integration\PDO;
use GR\Telephponic\Trace\Integration\Redis;
use GR\Telephponic\Trace\Telephponic;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Time\SystemClock;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

class Builder
{
    private ?TransportInterface $transport = null;
    private ?ResourceInfo $resourceInfo = null;
    private ?SamplerInterface $sampler = null;
    private ?ClockInterface $clock = null;
    private ?SpanExporterInterface $exporter = null;
    private bool $batchMode = false;
    private array $defaultAttributes = [];
    private $registerShutdown = true;
    private array $integrations = [];

    public function __construct(
        private readonly string $appName,
        private readonly ?string $namespace = null,
        private readonly string $environment = 'not-set',
    ) {
    }

    public static function get(
        string $appName,
        ?string $namespace = null,
        string $environment = 'not-set',
    ): self {
        return new self($appName, $namespace, $environment);
    }

    public function withDefaultAttributes(array $attributes): self
    {
        $this->defaultAttributes = $attributes;

        return $this;
    }

    public function addToDefaultAttribute(string $key, mixed $value): self
    {
        $this->defaultAttributes[$key] = $value;

        return $this;
    }

    public function enableShutDown(): self
    {
        $this->registerShutdown = true;

        return $this;
    }

    public function disableShutDown(): self
    {
        $this->registerShutdown = false;

        return $this;
    }

    public function forGrpcExportation(string $server): self
    {
        $endpoint = $server . OtlpUtil::method(Signals::TRACE);

        return $this
            ->withTransport((new GrpcTransportFactory())->create($endpoint))
            ->withExporter(new SpanExporter($this->transport))
        ;
    }

    private function withExporter(SpanExporterInterface $exporter): self
    {
        $this->exporter = $exporter;

        return $this;
    }

    private function withTransport(TransportInterface $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function enableBatchMode(): self
    {
        $this->batchMode = true;

        return $this;
    }

    public function disableBatchMode(): self
    {
        $this->batchMode = false;

        return $this;
    }

    public function forZipkinExportation(string $url, string $name = 'telephponic'): self
    {
        return $this
            ->withTransport(PsrTransportFactory::discover()->create($url, 'application/json'))
            ->withExporter(new Exporter($name, $this->transport))
        ;
    }

    public function enableShutdownHandler(): self
    {
        $this->enableShutdownHandler = true;

        return $this;
    }

    public function disableShutdownHandler(): self
    {
        $this->enableShutdownHandler = false;

        return $this;
    }

    public function withDefaultSpanExporter(): self
    {
        if ($this->transport === null) {
            throw new Exception('Transport not set'); // fixme Use a custom exception
        }

        return $this->withSpanExporter(new SpanExporter($this->transport));
    }

    private function withSpanExporter(SpanExporterInterface $spanExporter): self
    {
        $this->spanExporter = $spanExporter;

        return $this;
    }

    public function withInMemoryExporter(): self
    {
        return $this->withSpanExporter(new InMemoryExporter());
    }

    public function withTraceContextPropagator(): self
    {
        return $this->withPropagator(TraceContextPropagator::getInstance());
    }

    private function withPropagator(TextMapPropagatorInterface $propagator): self
    {
        $this->propagator = $propagator;

        return $this;
    }

    public function withSystemClock(): self
    {
        return $this->withClock(SystemClock::create());
    }

    public function withClock(ClockInterface $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    public function build(): Telephponic
    {
        if ($this->exporter === null) {
            throw new Exception('Exporter is not set'); // fixme Use a custom exception
        }

        if ($this->resourceInfo === null) {
            $this->withDefaultResource();
        }

        if ($this->clock === null) {
            $this->withDefaultClock();
        }

        if ($this->sampler === null) {
            $this->withAlwaysOnSampler();
        }

        $tracer = new TracerProvider(
            $this->batchMode
                ? $this->createBatchSpanProcessor()
                : $this->createSimpleSpanProcessor(),
            $this->sampler,
            $this->resourceInfo,
        );

        return new Telephponic(
            $tracer,
            $this->defaultAttributes,
            $this->registerShutdown,
            ...$this->integrations,
        );
    }

    /**
     * @param bool $enableAutoDiscover If true, Telephponic will add to all spans info retrieve from environment and server.
     */
    public function withDefaultResource(bool $enableAutoDiscover = false): self
    {
        $resourceInfo = ResourceInfo::create(
            Attributes::create([
                ResourceAttributes::SERVICE_NAMESPACE => $this->namespace ?? $this->appName,
                ResourceAttributes::SERVICE_NAME => $this->appName,
                ResourceAttributes::DEPLOYMENT_ENVIRONMENT => $this->environment,
            ])
        );
        if ($enableAutoDiscover) {
            $resourceInfo = ResourceInfoFactory::merge(
                $resourceInfo,
                ResourceInfoFactory::defaultResource(),
            );
        }

        return $this->withResourceInfo(
            $resourceInfo
        );
    }

    public function withResourceInfo(ResourceInfo $resourceInfo): self
    {
        $this->resourceInfo = $resourceInfo;

        return $this;
    }

    public function withDefaultClock(): self
    {
        return $this->withClock(ClockFactory::getDefault());
    }

    public function withAlwaysOnSampler(): self
    {
        return $this->withSampler(new AlwaysOnSampler());
    }

    public function withSampler(SamplerInterface $sampler): self
    {
        $this->sampler = $sampler;

        return $this;
    }

    private function createBatchSpanProcessor(): BatchSpanProcessor
    {
        return new BatchSpanProcessor($this->exporter, $this->clock);
    }

    private function createSimpleSpanProcessor(): SimpleSpanProcessor
    {
        return new SimpleSpanProcessor($this->exporter);
    }

    public function withProbabilitySampler(float $probability): self
    {
        return $this->withSampler(new TraceIdRatioBasedSampler($probability));
    }

    public function withNeverSampler(): self
    {
        return $this->withSampler(new AlwaysOffSampler());
    }

    public function withCurlIntegration(
        bool $traceCurlInit = false,
        bool $traceCurlExec = false,
        bool $traceCurlSetOpt = false
    ): self {
        return $this->withIntegration(
            new Curl(
                $traceCurlInit,
                $traceCurlExec,
                $traceCurlSetOpt
            )
        );
    }

    public function withIntegration(Integration $integration): self
    {
        $this->integrations[] = $integration;

        return $this;
    }

    public function withGrpcIntegration(): self
    {
        return $this->withIntegration(new Grpc());
    }

    public function withMemcachedIntegration(
        bool $traceAdd = false,
        bool $traceDelete = false,
        bool $traceDeleteMulti = false,
        bool $traceGet = false,
        bool $traceGetMulti = false,
        bool $traceSet = false,
        bool $traceSetMulti = false,
    ): self {
        return $this->withIntegration(
            new Memcached(
                $traceAdd,
                $traceDelete,
                $traceDeleteMulti,
                $traceGet,
                $traceGetMulti,
                $traceSet,
                $traceSetMulti,
            )
        );
    }

    public function withRedisIntegration(
        bool $traceConnect = false,
        bool $traceOpen = false,
        bool $tracePconnect = false,
        bool $tracePopen = false,
        bool $traceClose = false,
        bool $tracePing = false,
        bool $traceEcho = false,
        bool $traceGet = false,
        bool $traceSet = false,
        bool $traceDel = false,
        bool $traceDelete = false,
        bool $traceUnlink = false,
        bool $traceExists = false,
    ): self {
        return $this->withIntegration(
            new Redis(
                $traceConnect,
                $traceOpen,
                $tracePconnect,
                $tracePopen,
                $traceClose,
                $tracePing,
                $traceEcho,
                $traceGet,
                $traceSet,
                $traceDel,
                $traceDelete,
                $traceUnlink,
                $traceExists,
            )
        );
    }

    public function withPDOIntegration(): self
    {
        return $this->withIntegration(new PDO());
    }
}