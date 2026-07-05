<?php

namespace Tests\Feature;

use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;
use Illuminate\Support\Facades\Gate;
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
        $this->assertSame('ZonasCore API', config('scramble.info.title'));
        $this->assertSame('ZonasCore API', config('scramble.ui.title'));
        $this->assertSame('swagger', config('scramble.renderer'));
        $this->assertSame('api.swagger', config('scramble.renderers.swagger.view'));
        $this->assertSame(
            rtrim((string) config('app.url'), '/').'/api',
            config('scramble.servers.API'),
        );
    }

    public function test_documentation_ui_uses_swagger(): void
    {
        Gate::define('viewApiDocs', fn ($user = null): bool => true);

        $this->get('/docs/api')
            ->assertOk()
            ->assertSee('SwaggerUIBundle', false)
            ->assertSee('ZonasCore API');
    }
}
