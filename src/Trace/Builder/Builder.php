<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace\Builder;

use GR\Telephponic\Trace\Integration\Curl;
use GR\Telephponic\Trace\Integration\Grpc;
use GR\Telephponic\Trace\Integration\Integration;
use GR\Telephponic\Trace\Integration\Memcached;
use GR\Telephponic\Trace\Integration\PDO;
use GR\Telephponic\Trace\Integration\Redis;
use GR\Telephponic\Trace\Stacktrace\PlainTextStacktraceProvider;
use GR\Telephponic\Trace\Stacktrace\StacktraceProvider;
use GR\Telephponic\Trace\Telephponic;
use InvalidArgumentException;
use OpenTelemetry\API\Signals;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;
use RuntimeException;

class Builder
{
    private ?TransportInterface $transport = null;
    private ?ResourceInfo $resourceInfo = null;
    private ?SamplerInterface $sampler = null;
    private ?ClockInterface $clock = null;
    private ?SpanExporterInterface $exporter = null;
    private bool $batchMode = false;
    private array $defaultAttributes = [];
    private bool $registerShutdown = true;
    private ?StacktraceProvider $stacktraceProvider = null;
    private array $integrations = [];
    private TextMapPropagatorInterface $propagator;

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
        $mergeAttributeName = ResourceInfo::create(
            Attributes::create([
                ResourceAttributes::SERVICE_NAME => $name,
            ])
        );
        $currentResource = ResourceInfoFactory::defaultResource();
        $mergedResource = $currentResource->merge($mergeAttributeName);
        return $this
            ->withTransport(PsrTransportFactory::discover()->create($url, 'application/json'))
            ->withExporter(new ZipkinExporter($this->transport))
            ->withResourceInfo($mergedResource);
    }

    /** @throws RuntimeException */
    public function withDefaultSpanExporter(): self
    {
        if ($this->transport === null) {
            throw new RuntimeException('Transport not set'); // fixme Use a custom exception
        }

        return $this->withExporter(new SpanExporter($this->transport));
    }

    public function withInMemoryExporter(): self
    {
        return $this->withExporter(new InMemoryExporter());
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
        Clock::reset();
        return $this->withClock(Clock::getDefault());
    }

    public function withClock(ClockInterface $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    public function build(): Telephponic
    {
        if ($this->exporter === null) {
            throw new RuntimeException('Exporter is not set'); // fixme Use a custom exception
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
            $this->stacktraceProvider,
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
        return $this->withClock(Clock::getDefault());
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
        if ($probability < 0 || $probability > 1) {
            throw new InvalidArgumentException(
                sprintf('$probability must be between 0 and 1. You passed %s.', $probability)
            );
        }

        return $this->withSampler(new ParentBased(new TraceIdRatioBasedSampler($probability)));
    }

    public function withNeverSampler(): self
    {
        return $this->withSampler(new AlwaysOffSampler());
    }

    public function withCurlIntegration(
        bool $traceCurlInit = true,
        bool $traceCurlExec = true,
        bool $traceCurlSetOpt = true,
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

    public function withGrpcIntegration(
        bool $traceGrpcSimpleRequest = true,
        bool $traceGrpcClientStreamRequest = true,
        bool $traceGrpcServerStreamRequest = true,
        bool $traceGrpcBidiRequest = true,
    ): self {
        return $this->withIntegration(
            new Grpc(
                $traceGrpcSimpleRequest,
                $traceGrpcClientStreamRequest,
                $traceGrpcServerStreamRequest,
                $traceGrpcBidiRequest,
            )
        );
    }

    public function withMemcachedIntegration(
        bool $traceAdd = true,
        bool $traceDelete = true,
        bool $traceDeleteMulti = true,
        bool $traceGet = true,
        bool $traceGetMulti = true,
        bool $traceSet = true,
        bool $traceSetMulti = true,
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
        bool $traceConnect = true,
        bool $traceOpen = true,
        bool $tracePconnect = true,
        bool $tracePopen = true,
        bool $traceClose = true,
        bool $tracePing = true,
        bool $traceEcho = true,
        bool $traceGet = true,
        bool $traceSet = true,
        bool $traceDel = true,
        bool $traceDelete = true,
        bool $traceUnlink = true,
        bool $traceExists = true,
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

    public function withPDOIntegration(
        bool $tracePdoConnect = true,
        bool $tracePdoQuery = true,
        bool $tracePdoCommit = true,
        bool $tracePdoStatementQuery = true,
        bool $tracePdoStatementBindParam = true,
    ): self {
        return $this->withIntegration(
            new PDO(
                $tracePdoConnect,
                $tracePdoQuery,
                $tracePdoCommit,
                $tracePdoStatementQuery,
                $tracePdoStatementBindParam,
            )
        );
    }

    public function enableAddTraceAsAttribute(): self
    {
        return $this->withPlainTextStacktraceProvider();
    }

    public function withPlainTextStacktraceProvider(): self
    {
        return $this->withStacktraceProvider(new PlainTextStacktraceProvider());
    }

    public function withStacktraceProvider(StacktraceProvider $stacktraceProvider): self
    {
        $this->stacktraceProvider = $stacktraceProvider;

        return $this;
    }

    public function disableAddTraceAsAttribute(): self
    {
        $this->stacktraceProvider = null;

        return $this;
    }
}
