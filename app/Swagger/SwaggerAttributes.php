<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(title: "ManagemenLogbook API", version: "1.0.0", description: "API documentation for ManagemenLogbook")]
#[OA\SecurityScheme(securityScheme: "bearerAuth", type: "http", scheme: "bearer", bearerFormat: "JWT")]
#[OA\PathItem(
    path: "/",
    get: new OA\Get(summary: "Root", responses: [new OA\Response(response: 200, description: "OK")])
)]
class SwaggerAttributes {}
