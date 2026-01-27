<?php
require __DIR__ . '/vendor/autoload.php';
try {
    $openapi = \OpenApi\Generator::scan([__DIR__ . '/app/Http/Controllers/Api/AuthController.php']);
    echo json_encode($openapi, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Trace:\n" . $e->getTraceAsString() . PHP_EOL;
}
