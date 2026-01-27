<?php
require __DIR__ . '/vendor/autoload.php';

use OpenApi\Generator;

try {
    $openapi = \OpenApi\Generator::scan([__DIR__ . '/app']);
    echo json_encode($openapi, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo "ERROR: " . get_class($e) . " - " . $e->getMessage() . PHP_EOL;
    echo "Trace:\n" . $e->getTraceAsString() . PHP_EOL;
}
