<?php

namespace Tests\Feature;

use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OpenApiDocumentationTest extends TestCase
{
    public function test_openapi_routes_and_security_are_configured(): void
    {
        $uiRoute = Route::getRoutes()->getByName('scramble.docs.ui');
        $documentRoute = Route::getRoutes()->getByName('scramble.docs.document');

        $this->assertNotNull($uiRoute);
        $this->assertNotNull($documentRoute);
        $this->assertSame('docs/api', $uiRoute->uri());
        $this->assertSame('docs/api.json', $documentRoute->uri());
        $this->assertSame(
            MiddlewareAuthSecurityStrategy::class,
            config('scramble.security_strategy'),
        );
        $this->assertSame('API Logistica MVP', config('scramble.ui.title'));
    }
}
