# Idea del proyecto

API REST para gestionar envios asignados a choferes.

## Objetivo

Crear un backend en Laravel para que usuarios administradores puedan crear choferes, crear envios y asignarlos. Los choferes deben poder iniciar sesion, ver sus envios asignados, aceptar pedidos y cambiar el estado del envio hasta finalizado o entregado.

## Usuarios

- Admin: crea usuarios chofer, crea envios y asigna envios a choferes.
- Chofer: inicia sesion, ve sus envios asignados, acepta un envio y actualiza su estado.

## Flujo principal

1. Existe un admin inicial creado por seeder o comando.
2. El admin inicia sesion con email y password.
3. El admin crea usuarios choferes.
4. El admin crea envios.
5. El admin asigna cada envio a un chofer.
6. El chofer inicia sesion con email y password.
7. El chofer ve sus envios asignados.
8. El chofer acepta un envio.
9. El chofer cambia el estado a finalizado o entregado.

## Autenticacion

- Se usara Laravel Sanctum.
- Los usuarios se autentican con email y password.
- Al hacer login, Laravel devuelve un token.
- Las rutas protegidas reciben el token con Authorization: Bearer <token>.
- La tabla personal_access_tokens guarda los tokens generados por Sanctum.

## Roles

Agregar un campo role a la tabla users.

Roles iniciales:

- admin
- chofer

## Recuperacion de contrasena

Primera version simple:

- El admin puede resetear la contrasena de un chofer.
- Si se olvida la contrasena del admin principal, se resetea con un seeder o comando artisan.

Mas adelante se puede agregar recuperacion por email.

## Estado tecnico actual

- Login con Sanctum implementado: POST /api/login.
- Usuario autenticado implementado: GET /api/profile.
- Logout implementado: POST /api/logout.
- Campo role agregado a users.
- Admin inicial creado por DatabaseSeeder: admin@admin.com.
- Proyecto Laravel creado.
- MySQL configurado en .env con la base prog3_backend.
- Laravel Sanctum instalado con php artisan install:api.
- Migraciones ejecutadas.
- Tabla personal_access_tokens creada.
- Modelo User usa Laravel\Sanctum\HasApiTokens.
- Ruta de prueba disponible: GET /api/health.
- Middleware de roles implementado: role:admin.
- Rutas admin protegidas con auth:sanctum y role:admin.
- CRUD de choferes implementado para administradores.
- Choferes tienen nombre, apellido, DNI, fecha de nacimiento, licencia, email y telefono.

## Proximos pasos

1. Crear modelo, migracion y endpoints de envios.
2. Crear endpoints del chofer para aceptar y actualizar estado de envios.
3. Agregar pruebas de integracion cuando el entorno tenga driver de base de datos para testing.

## Endpoints pensados

Autenticacion:

- POST /api/login
- POST /api/logout
- GET /api/profile

Admin:

- POST /api/admin/choferes
- GET /api/admin/choferes
- GET /api/admin/choferes/{id}
- PUT/PATCH /api/admin/choferes/{id}
- DELETE /api/admin/choferes/{id}
- PATCH /api/admin/choferes/{id}/password
- POST /api/admin/envios
- GET /api/admin/envios
- PATCH /api/admin/envios/{id}/asignar

Chofer:

- GET /api/chofer/envios
- PATCH /api/chofer/envios/{id}/aceptar
- PATCH /api/chofer/envios/{id}/estado

## Estados posibles de un envio

- pendiente
- asignado
- aceptado
- en_camino
- entregado
- finalizado
- cancelado
