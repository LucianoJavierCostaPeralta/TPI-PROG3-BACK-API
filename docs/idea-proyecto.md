# Idea del proyecto

API REST para gestionar envios asignados a choferes.

## Objetivo

Crear un backend en Laravel para que usuarios administradores puedan crear choferes, crear envios y asignarlos. Los choferes deben poder iniciar sesion, ver sus envios asignados, aceptar pedidos y cambiar el state del envio hasta finished o delivered.

## Usuarios

- Admin: crea usuarios chofer, crea envios y asigna envios a choferes.
- Chofer: inicia sesion, ve sus envios asignados, acepta un envio y actualiza su state.

## Flujo principal

1. Existe un admin inicial creado por seeder o comando.
2. El admin inicia sesion con email y password.
3. El admin crea usuarios choferes.
4. El admin crea envios.
5. El admin asigna cada envio a un chofer.
6. El chofer inicia sesion con email y password.
7. El chofer ve sus envios asignados.
8. El chofer acepta un envio.
9. El chofer cambia el state a on_the_way, delivered o finished.

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

- Login con Sanctum implementado: POST /api/v1/login.
- Usuario autenticado implementado: GET /api/v1/profile.
- Logout implementado: POST /api/v1/logout.
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
- Modelo, migracion y endpoints admin de envios implementados.
- Envios tienen direccion de origen, direccion de destino, descripcion, state y chofer asignado opcional.
- Endpoints de chofer para listar, aceptar y actualizar state de envios implementados.

## Proximos pasos

1. Probar flujo completo con cliente externo: admin crea/asigna envio y chofer acepta/actualiza state.
2. Agregar pruebas de integracion cuando el entorno tenga driver de base de datos para testing.

## Endpoints pensados

Autenticacion:

- POST /api/v1/login
- POST /api/v1/logout
- GET /api/v1/profile

Admin:

- POST /api/v1/admin/choferes
- GET /api/v1/admin/choferes
- GET /api/v1/admin/choferes/{id}
- PUT/PATCH /api/v1/admin/choferes/{id}
- DELETE /api/v1/admin/choferes/{id}
- PATCH /api/v1/admin/choferes/{id}/password
- POST /api/v1/admin/entregas
- GET /api/v1/admin/entregas
- PATCH /api/v1/admin/entregas/{id}/assign

Chofer:

- GET /api/v1/chofer/entregas
- PATCH /api/v1/chofer/entregas/{id}/accept
- PATCH /api/v1/chofer/entregas/{id}/state

## States posibles de un envio

- pending
- assigned
- accepted
- on_the_way
- delivered
- finished
- cancelled
