<?php
require __DIR__ . '/vendor/autoload.php';
try {
    $openapi = \OpenApi\Generator::scan([__DIR__ . '/app/Swagger/SwaggerAnnotations.php']);
    echo json_encode($openapi, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERROR: " . get_class($e) . " - " . $e->getMessage() . PHP_EOL;
    echo "Trace:\n" . $e->getTraceAsString() . PHP_EOL;
}
