<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "ABG Social Media API",
    description: "API documentation for the ABG Social Media application",
    contact: new OA\Contact(email: "admin@example.com")
)]
#[OA\Server(
        url: "http://localhost:8000",
        description: "Local API Server"
    )]
#[OA\SecurityScheme(
        securityScheme: "apiAuth",
        type: "http",
        scheme: "bearer",
        bearerFormat: "JWT",
        description: "Enter token in format (Bearer <token>)"
    )]
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
