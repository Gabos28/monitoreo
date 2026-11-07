<?php
namespace App\GraphQL\Queries;
use App\Services\ServerMetrics;

final class Metrics
{
public function __construct(private ServerMetrics $metrics) {}
public function __invoke(): array
{
return $this->metrics->snapshot(); // el mismo servicio del Paso 2
}
}

query {
metrics {
hostname
cpu_temp_c
memory {
used_pct
}
}
}

subscription {
metricsUpdated {
cpu_temp_c
}
}
