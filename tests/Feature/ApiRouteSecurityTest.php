<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiRouteSecurityTest extends TestCase
{
    #[DataProvider('protectedRoutes')]
    public function test_active_private_routes_require_authentication(string $method, string $uri): void
    {
        $this->json($method, $uri)->assertUnauthorized();
    }

    #[DataProvider('retiredLegacyRoutes')]
    public function test_legacy_crud_routes_are_not_exposed(string $uri): void
    {
        $this->getJson($uri)->assertNotFound();
    }

    public static function protectedRoutes(): array
    {
        return [
            ['GET', '/api/v1/empresas'],
            ['GET', '/api/v1/users'],
            ['GET', '/api/v1/estados-entrega'],
            ['GET', '/api/v1/admin/resumen'],
            ['GET', '/api/v1/admin/choferes'],
            ['GET', '/api/v1/admin/entregas'],
            ['GET', '/api/v1/chofer/entregas'],
        ];
    }

    public static function retiredLegacyRoutes(): array
    {
        return [
            ['/api/v1/clientes-destinatarios'],
            ['/api/v1/productos'],
            ['/api/v1/entregas'],
            ['/api/v1/detalles-entrega'],
            ['/api/v1/comprobantes-entrega'],
            ['/api/v1/tipos-vehiculo'],
            ['/api/v1/vehiculos'],
            ['/api/v1/asignaciones-vehiculos'],
            ['/api/v1/zonas-cobertura'],
            ['/api/v1/chofer-zonas'],
            ['/api/v1/jornadas-trabajo'],
            ['/api/v1/motivos-rechazo'],
            ['/api/v1/roles'],
            ['/api/v1/notificaciones'],
            ['/api/v1/auditoria-logs'],
        ];
    }
}
