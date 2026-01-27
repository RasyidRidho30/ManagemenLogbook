<?php
require __DIR__ . '/vendor/autoload.php';
try {
    $openapi = \OpenApi\Generator::scan([__DIR__ . '/app'], ['validate' => false]);
    echo json_encode($openapi, JSON_PRETTY_PRINT) . PHP_EOL;
    // debug counts
    $paths = property_exists($openapi, 'paths') ? $openapi->paths : null;
    echo "Paths type: " . gettype($paths) . "\n";
    if (is_array($paths) || $paths instanceof Traversable) {
        $count = 0;
        foreach ($paths as $p) {
            $count++;
        }
        echo "Found paths: $count\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . get_class($e) . " - " . $e->getMessage() . PHP_EOL;
    echo "Trace:\n" . $e->getTraceAsString() . PHP_EOL;
}
