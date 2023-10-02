<?php

declare(strict_types=1);

use GR\Telephponic\Trace\Builder\Builder;

error_reporting(E_ALL & ~E_NOTICE);

include_once __DIR__ . '/../vendor/autoload.php';

$url = 'http://zipkin:9411/api/v2/spans';
$builder = new Builder('freepik-search-engine', 'search-engine');
$builder->disableBatchMode()
        ->disableShutDown()
        ->forZipkinExportation($url)
        ->withProbabilitySampler(1)
        ->withRedisIntegration(true, true, true, true, true, true, true)
        ->withCurlIntegration(true, true, true)
        ->withPDOIntegration()
;

$tracer = $builder->build();

// $tracer->start('search-engine');
// $tracer->start('search-engine-child');
// $tracer->end('search-engine-child');
// $tracer->end('search-engine');

$tracer->addWatcherToFunction('sleep', static function (int $time): array {
    return [
        'sleep' => [
            'time' => $time,
        ],
    ];
});

sleep(3);

$tracer->shutdown();





