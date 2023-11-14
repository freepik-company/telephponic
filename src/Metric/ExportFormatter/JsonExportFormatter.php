<?php

declare(strict_types=1);

namespace GR\Telephponic\Metric\ExportFormatter;

use GR\Telephponic\Metric\Contract\ExportFormatter;
use GR\Telephponic\Metric\Type\Metric;
use JsonException;

class JsonExportFormatter implements ExportFormatter
{

    public function __construct(private readonly ?string $wrapperKey = 'metric')
    {
    }

    public function export(Metric $metric): string
    {
        $normalized = (null !== $this->wrapperKey)
            ? [$this->wrapperKey => $metric->normalize()]
            : $metric->normalize();

        try {
            $json = json_encode($normalized, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // fixme: Should it be raised?
            $json = null !== $this->wrapperKey
                ? sprintf("%s: {}", $this->wrapperKey)
                : '{}';
        } finally {
            return $json;
        }
    }
}
