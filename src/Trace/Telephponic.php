<?php

declare(strict_types=1);

namespace GR\Telephponic\Trace;

use GR\Telephponic\Trace\Integration\Integration;
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
    private array $integrations = [];
    private ScopeInterface $scope;
    private TracerInterface $tracer;
    private SpanInterface $root;

    public function __construct(
        private readonly TracerProviderInterface $tracerProvider,
        private readonly array $defaultAttributes = [],
        private readonly bool $registerShutdown = true,
        Integration ...$integrations
    ) {
        $this->tracer = $this->tracerProvider->getTracer('telephponic-tracer');
        $rootName = $_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0] ?? 'unknown';

        $root = $this->getSpan($rootName);
        $root->setAttributes($this->getRootAttributes());
        $this->scope = $root->activate();
        $this->root = $root;

        if ($this->registerShutdown) {
            register_shutdown_function([$this, 'shutdown']);
        }

        $this->addIntegrations(...$integrations);
    }

    private function getSpan(string $name): SpanInterface
    {
        return $this->spans[$name] ?? $this->tracer->spanBuilder($name)->startSpan();
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

    public function addEvent(string $name, string $eventName, array $attributes = []): void
    {
        $span = $this->spans[$name] ?? $this->getSpan($name);
        $span->addEvent($eventName, $attributes);
    }

    public function sendTraces(): void
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
        $span = $this->spans[$name] ?? null;
        $scope = $this->scopes[$name] ?? null;
        unset($this->spans[$name], $this->scopes[$name]);
        $span?->end();
        $scope?->detach();
    }

    public function shutdown(): void
    {
        $this->sendTraces();
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

        $name = (null !== $class)
            ? $class . '::' . $method
            : $method;

        hook(
            $class,
            $method,
            pre: function (
                mixed $object,
                array $params,
                ?string $class,
                string $function,
                ?string $filename,
                ?int $lineNumber
            ) use (
                $name,
                $closure
            ) {
                if (null !== $object) {
                    $parameters = array_merge([$object], $params);
                } else {
                    $parameters = $params;
                }

                $parameters = call_user_func($closure, ...$parameters);
                $this->start($name, $parameters);
            },
            post: function (mixed $object, array $parameters, mixed $returnValue, ?Throwable $exception) use ($name) {
                $span = $this->getSpan($name);

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
        $span = $this->getSpan($name);
        $span->recordException($throwable);
    }

    public function addWatcherToFunction(string $function, $closure): void
    {
        $this->createHook(null, $function, $closure);
    }

    public function addAttributes(string $name, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->addAttribute($name, $key, $value);
        }
    }

    public function addAttribute(string $name, string $key, mixed $value): void
    {
        $span = $this->getSpan($name);
        $span->setAttribute($key, $value);
    }

}
