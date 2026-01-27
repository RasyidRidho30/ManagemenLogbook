<?php

namespace App\Swagger;

/**
 * @oa\Info(
 *   title="ManagemenLogbook API",
 *   version="1.0.0",
 *   description="API documentation for ManagemenLogbook"
 * )
 *
 * @oa\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */

/**
 * @\OpenApi\Annotations\OpenApi(
 *   @\OpenApi\Annotations\Info(
 *     title="ManagemenLogbook API",
 *     version="1.0.0",
 *     description="API documentation for ManagemenLogbook"
 *   ),
 *   @\OpenApi\Annotations\PathItem(
 *     path="/",
 *     @\OpenApi\Annotations\Get(
 *       summary="Root",
 *       @\OpenApi\Annotations\Response(response=200, description="OK")
 *     )
 *   ),
 *   @\OpenApi\Annotations\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 *   )
 * )
 */
class SwaggerAnnotations {}
