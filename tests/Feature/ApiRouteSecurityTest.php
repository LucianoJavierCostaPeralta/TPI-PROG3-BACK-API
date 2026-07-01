<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiRouteSecurityTest extends TestCase
{
    #[DataProvider('protectedLegacyRoutes')]
    public function test_legacy_crud_routes_require_authentication(string $method, string $uri): void
    {
        $this->json($method, $uri)->assertUnauthorized();
    }

    public static function protectedLegacyRoutes(): array
    {
        return [
            ['GET', '/api/v1/empresas'],
            ['GET', '/api/v1/clientes-destinatarios'],
            ['GET', '/api/v1/productos'],
            ['GET', '/api/v1/entregas'],
            ['GET', '/api/v1/detalles-entrega'],
            ['GET', '/api/v1/estados-entrega'],
            ['GET', '/api/v1/comprobantes-entrega'],
            ['GET', '/api/v1/tipos-vehiculo'],
            ['GET', '/api/v1/vehiculos'],
            ['GET', '/api/v1/asignaciones-vehiculos'],
            ['GET', '/api/v1/zonas-cobertura'],
            ['GET', '/api/v1/chofer-zonas'],
            ['GET', '/api/v1/jornadas-trabajo'],
            ['GET', '/api/v1/motivos-rechazo'],
            ['GET', '/api/v1/roles'],
            ['GET', '/api/v1/users'],
            ['GET', '/api/v1/notificaciones'],
            ['GET', '/api/v1/auditoria-logs'],
            ['GET', '/api/v1/solicitudes-asesoramiento'],
        ];
    }
}
