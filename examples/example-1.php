<?php

declare(strict_types=1);

use GR\Telephponic\Trace\Builder\Builder;

error_reporting(E_ALL & ~E_NOTICE);

include_once __DIR__ . '/../vendor/autoload.php';

// $url = 'http://zipkin:9411/api/v2/spans';
$url = 'http://jaeger:4317';
$builder = new Builder('freepik-search-engine', 'search-engine');
$builder->enableBatchMode()
        ->disableShutDown()
    //->forZipkinExportation($url)
        ->forGrpcExportation($url)
        ->withProbabilitySampler(1)
        ->withRedisIntegration(true, true, true, true, true, true, true)
        ->withCurlIntegration(true, true, true)
        ->withPDOIntegration()
;

$tracer = $builder->build();

$tracer->addWatcherToFunction('sleep', static function (int $time): array {
    return [
        'sleep' => [
            'time' => $time,
        ],
    ];
});

$longText = str_repeat('a', 1000);

$tracer->start('search-engine', ['long.text' => $longText]);
$tracer->addEvent('search-engine-started', ['search-engine' => 'started']);
sleep(1);
$tracer->start('search-engine-child');
sleep(2);
$tracer->addException(new RuntimeException('test exception'));
$tracer->end();
$tracer->end();

$tracer->shutdown();





