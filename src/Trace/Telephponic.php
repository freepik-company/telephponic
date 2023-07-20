<?php

declare(strict_types=1);


namespace Muriano\Telephponic\Trace;


use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\ScopeInterface;
use RuntimeException;
use Throwable;

use function OpenTelemetry\Instrumentation\hook;

class Telephponic
{
    /** @var array<string, SpanInterface> */
    private array $spans = [];
    private array $scopes = [];
    private ScopeInterface $scope;
    private TracerInterface $tracer;
    private SpanInterface $root;

    public function __construct(
        private readonly TracerProviderInterface $tracerProvider,
        private readonly array $defaultAttributes = [],
        private readonly bool $registerShutdown = true,
    ) {
        $this->tracer = $this->tracerProvider->getTracer('telephponic-trace');
        $rootName = $_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0] ?? 'unknown';

        $root = $this->getSpan($rootName);
        $root->setAttributes($this->getRootAttributes());
        $this->scope = $root->activate();
        $this->root = $root;

        if ($this->registerShutdown) {
            register_shutdown_function([$this, 'shutdown']);
        }
    }

    private function getSpan(string $name): SpanInterface
    {
        return $this->tracer->spanBuilder($name)->startSpan();
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

    public function addEvent(string $name, string $eventName, array $attributes = []): void
    {
        $span = $this->spans[$name] ?? $this->getSpan($name);
        $span->addEvent($eventName, $attributes);
    }

    public function shutdown(): void
    {
        foreach ($this->spans as $name => $span) {
            $span->setAttribute('autoclosed', 'true');
            $span->end();
            $scope = $this->scopes[$name];
            $scope->detach();
        }

        $this->root->end();
        $this->scope->detach();
        $this->tracerProvider->shutdown();
    }

    public function end(string $name): void
    {
        $span = $this->spans[$name];
        $scope = $this->scopes[$name];
        unset($this->spans[$name], $this->scopes[$name]);
        $span->end();
        $scope->detach();
    }

    public function addWatcherToMethod(string $class, string $method): void
    {
        $this->createHook($class, $method);
    }

    public function createHook(?string $class, string $method): void
    {
        if (!extension_loaded('opentelemetry')) {
            throw new RuntimeException('OpenTelemetry extension is not loaded');
        }

        $name = (null !== $class)
            ? $class . '::' . $method
            : $method;

        hook(
            $class,
            $method,
            function () use ($name) {
                $this->start($name);
            },
            function (mixed $object, array $parameters, mixed $returnValue, ?Throwable $exception) use ($name) {
                $span = $this->spans[$name] ?? $this->getSpan($name);
                $span->setAttributes(
                    [
                        'parameters' => $parameters,
                    ]
                );

                if ($exception !== null) {
                    $span->recordException($exception);
                }

                $this->end($name);
            }
        );
    }

    public function start(string $name, array $attributes = []): void
    {
        $span = $this->getSpan($name);
        $scope = $span->activate();
        $span->setAttributes($this->defaultAttributes + $attributes);
        $this->spans[$name] = $span;
        $this->scopes[$name] = $scope;
    }

    public function addException(string $name, Throwable $throwable): void
    {
        $span = $this->spans[$name] ?? $this->getSpan($name);
        $span->recordException($throwable);
    }

    public function addWatcherToFunction(string $function): void
    {
        $this->createHook(null, $function);
    }
}