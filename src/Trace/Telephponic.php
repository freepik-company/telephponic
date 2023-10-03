<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace;

use GR\Telephponic\Trace\Integration\Integration;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageScopeInterface;
use RuntimeException;
use Throwable;

use function OpenTelemetry\Instrumentation\hook;

class Telephponic
{
    private array $integrations = [];
    private TracerInterface $tracer;

    public function __construct(
        private readonly TracerProviderInterface $tracerProvider,
        private readonly array $defaultAttributes = [],
        private readonly bool $registerShutdown = true,
        Integration ...$integrations
    ) {
        $this->tracer = $this->tracerProvider->getTracer('telephponic-tracer');
        $rootName = $_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0] ?? 'unknown';

        $this->start($rootName, $this->getRootAttributes());

        if ($this->registerShutdown) {
            register_shutdown_function([$this, 'shutdown']);
        }

        $this->addIntegrations(...$integrations);
    }

    public function start(string $name, array $attributes = []): void
    {
        $span = $this->tracer->spanBuilder($name)->startSpan();
        $span->setAttributes($this->defaultAttributes + $attributes);
        Context::storage()->attach($span->storeInContext(Context::getCurrent()));
    }

    private function getRootAttributes(): array
    {
        $attributes = $this->defaultAttributes;

        if (isset($_SERVER['REQUEST_URI'])) {
            $attributes['http.url'] = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $attributes['http.method'] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $attributes['http.host'] = $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $attributes['http.user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $attributes['http.referer'] = $_SERVER['HTTP_REFERER'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $attributes['http.x_forwarded_for'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $attributes;
    }

    private function addIntegrations(Integration ...$integrations): void
    {
        foreach ($integrations as $integration) {
            $this->addIntegration($integration);
        }
    }

    public function addIntegration(Integration $integration): void
    {
        if ($this->integrations[$integration::class] ?? false) {
            return;
        }

        $integration->load($this);
        $this->integrations[$integration::class] = true;
    }

    public function addEvent(string $eventName, array $attributes = []): void
    {
        $scope = $this->getScope();
        if (null === $scope) {
            return;
        }
        $span = Span::fromContext($scope->context());
        $span->addEvent($eventName, $attributes);
    }

    /**
     * @return null|ContextStorageScopeInterface
     */
    public function getScope(): ?ContextStorageScopeInterface
    {
        return Context::storage()->scope();
    }

    public function sendTraces(): void
    {
        $this->tracerProvider->shutdown();
    }

    public function shutdown(): void
    {
        $this->getSpan()->end();
        $this->getScope()?->detach();
        $this->sendTraces();
    }

    public function end(): void
    {
        $scope = $this->getScope();
        $this->getSpan()->end();
        $scope?->detach();
    }

    public function getSpan(): SpanInterface
    {
        return Span::fromContext($this->getScope()?->context());
    }

    public function addWatcherToMethod(string $class, string $method, $closure): void
    {
        $this->createHook($class, $method, $closure);
    }

    public function createHook(?string $class, string $method, $closure): void
    {
        if (!extension_loaded('opentelemetry')) {
            throw new RuntimeException('OpenTelemetry extension is not loaded');
        }

        hook(
            $class,
            $method,
            pre: function (
                mixed $object,
                ?array $params,
                ?string $class,
                ?string $function,
                ?string $filename,
                ?int $lineNumber
            ) use (
                $closure
            ) {
                $name = null !== $class
                    ? $class . '::' . $function
                    : $function;

                $params ??= [];
                $parameters = null === $object
                    ? $params
                    : array_merge([$object], $params);

                $this->start($name, $closure(...$parameters));
            },
            post: function (
                mixed $object,
                ?array $params,
                mixed $returnValue,
                ?Throwable $exception
            ) use (
                $closure
            ) {
                $params ??= [];
                $parameters = null === $object
                    ? $params
                    : array_merge([$object], $params);

                $span = $this->getSpan();

                $span->setAttributes($closure(...$parameters));

                $exception && $span->recordException($exception);

                $span->setStatus(
                    $exception
                        ? StatusCode::STATUS_ERROR
                        : StatusCode::STATUS_OK
                );

                $this->end();
            }
        );
    }

    public function addException(Throwable $throwable): void
    {
        $this->getSpan()->recordException($throwable);
    }

    public function addWatcherToFunction(string $function, $closure): void
    {
        $this->createHook(null, $function, $closure);
    }

    public function addAttribute(string $key, mixed $value): void
    {
        $this->addAttributes([$key => $value]);
    }

    public function addAttributes(array $attributes): void
    {
        $span = $this->getSpan();
        foreach ($attributes as $key => $value) {
            $span->setAttribute($key, $value);
        }
    }

}
