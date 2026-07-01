# Pruebas manuales del MVP con Insomnia

## Preparacion

1. Levantar la API y configurar un entorno de Insomnia con `base_url`, por ejemplo `http://127.0.0.1:8000`.
2. Ejecutar las migraciones y seeders sobre una base MySQL de prueba: `php artisan migrate:fresh --seed`.
3. Crear las variables `admin_token`, `chofer_token`, `chofer_id` y `entrega_id`.
4. En las peticiones protegidas usar `Authorization: Bearer <token>` y `Accept: application/json`.
5. Usar un CUIT y correos nuevos en cada corrida para evitar las restricciones de unicidad.

## Secuencia principal

### 1. Salud

`GET {{ base_url }}/api/health`

Resultado esperado: `200` y `status: ok`.

### 2. Registro de empresa

`POST {{ base_url }}/api/v1/registro`

```json
{
  "razon_social": "Transportes Insomnia",
  "cuit": "20987654321",
  "nombre_completo": "Administrador Insomnia",
  "email": "admin.insomnia@example.com",
  "password": "123456",
  "telefono": "3515550100",
  "tamano_flota": "1-10",
  "terminos_aceptados": true
}
```

Resultado esperado: `201`, empresa y administrador creados, y `token`. Guardar el token como `admin_token`.

### 3. Login administrativo

`POST {{ base_url }}/api/v1/login`

```json
{
  "email": "admin.insomnia@example.com",
  "password": "123456"
}
```

Resultado esperado: `200`, rol `Admin` y un token Sanctum.

### 4. Crear chofer

`POST {{ base_url }}/api/v1/admin/choferes`

```json
{
  "nombre_completo": "Juan Perez",
  "dni": "12345678",
  "fecha_nacimiento": "1990-05-12",
  "email": "chofer.insomnia@example.com",
  "telefono": "3515550200",
  "password": "123456"
}
```

Resultado esperado: `201`. Guardar `data.id` como `chofer_id`.

### 5. Login del chofer

`POST {{ base_url }}/api/v1/login`

```json
{
  "email": "chofer.insomnia@example.com",
  "password": "123456"
}
```

Resultado esperado: `200`, rol `Chofer`. Guardar `token` como `chofer_token`.

### 6. Crear entrega MVP

`POST {{ base_url }}/api/v1/admin/entregas`

```json
{
  "cliente": "Comercio Centro",
  "cliente_dni": "30123456",
  "producto": "Caja de documentos",
  "direccion_destino": "Av. Colon 1234",
  "referencia": "Entregar por recepcion"
}
```

Resultado esperado: `201`, `estado_id: 1`, `chofer_id: null`. Guardar `data.id` como `entrega_id`.

### 7. Verificar listado, filtros y paginacion

Ejecutar con `admin_token`:

- `GET {{ base_url }}/api/v1/admin/entregas?estado_id=1&per_page=10`: contiene la entrega nueva.
- `GET {{ base_url }}/api/v1/admin/entregas?sin_chofer=1`: contiene solamente entregas sin chofer.
- `GET {{ base_url }}/api/v1/admin/entregas?chofer_id={{ chofer_id }}`: no contiene la entrega antes de asignarla.

Resultado esperado: `200`; el paginador se encuentra en `data` y los registros en `data.data`.

### 8. Asignar entrega

`PATCH {{ base_url }}/api/v1/admin/entregas/{{ entrega_id }}/assign`

```json
{
  "chofer_id": "{{ chofer_id }}"
}
```

Resultado esperado: `200`, `estado_id: 2`, chofer asignado e historial `pending -> assigned` visible en el detalle.

### 9. Ciclo del chofer

Usar `chofer_token`:

1. `GET {{ base_url }}/api/v1/chofer/entregas`: contiene solo la entrega asignada al chofer.
2. `PATCH {{ base_url }}/api/v1/chofer/entregas/{{ entrega_id }}/accept`: devuelve `estado_id: 3`.
3. `PATCH {{ base_url }}/api/v1/chofer/entregas/{{ entrega_id }}/state` con `{"estado_id": 4}`.
4. Repetir el cambio de estado con `{"estado_id": 5, "cliente_dni": "30123456"}`; un DNI faltante o incorrecto debe responder `422` sin cambiar el estado.
5. Repetir el cambio de estado con `{"estado_id": 6}`.
6. `GET {{ base_url }}/api/v1/chofer/entregas/{{ entrega_id }}`: verifica el historial completo y ordenado.

Todos los cambios deben responder `200`. Un salto no secuencial debe responder `422`.

## Casos de seguridad y aislamiento

- Repetir un CRUD heredado sin token, por ejemplo `GET /api/v1/productos`: debe responder `401`.
- Repetirlo con `chofer_token`: debe responder `403`.
- Consultar una entrega o un chofer perteneciente a otra empresa con `admin_token`: debe responder `404`.
- Asignar un `chofer_id` de otra empresa: debe responder `422`.
- Combinar `chofer_id` con `sin_chofer=1`: debe responder `422`.

## Restricciones del contrato MVP

No enviar `fecha_programada`, `cantidad`, `latitud` ni `longitud`. `cliente`, `cliente_dni` y `producto` son campos directos de la entrega. El chofer no recibe el DNI esperado y debe ingresarlo para marcarla como entregada.
