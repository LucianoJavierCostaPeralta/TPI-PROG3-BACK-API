# Plan de integracion entre API y aplicacion movil

## Objetivo

Este documento centraliza el estado actual del backend Laravel, el contrato esperado por la aplicacion React Native, las reglas de negocio y las tareas pendientes.

## Flujo principal del sistema

1. Una empresa completa el formulario de registro.
2. El sistema crea la empresa y su usuario administrador en una misma transaccion.
3. El administrador inicia sesion y gestiona exclusivamente los datos de su empresa.
4. El administrador crea choferes, clientes, productos y entregas.
5. Una entrega se crea junto con sus productos y cantidades en una transaccion maestro-detalle.
6. El administrador asigna o desasigna una entrega a un chofer de su empresa.
7. El chofer consulta solamente sus entregas asignadas.
8. El chofer acepta una entrega y actualiza su estado.
9. Cada cambio de estado genera un registro de historial dentro de la misma transaccion.
10. Las operaciones relevantes generan registros de auditoria automaticos.

## Actores y permisos

### Administrador

- Pertenece a una empresa.
- Gestiona choferes, administradores, clientes, productos, vehiculos, zonas y entregas de su empresa.
- Asigna y desasigna entregas a choferes de su empresa.
- No puede consultar ni modificar informacion de otras empresas.

### Chofer

- Pertenece a una empresa.
- Consulta solamente sus entregas asignadas.
- Acepta entregas y realiza las transiciones de estado permitidas.
- No accede a operaciones administrativas.

### Asesor

El frontend contempla este rol, pero sus permisos todavia no estan definidos y el backend no lo implementa.

## Endpoints publicos y protegidos

Deberian permanecer publicos solamente:

- `GET /api/health`
- `POST /api/v1/login`
- `POST /api/v1/registro`
- Solicitud de recuperacion de contrasena.
- Confirmacion de nueva contrasena mediante token.

El resto de los endpoints debe requerir un token Sanctum y el rol correspondiente.

## Contrato enviado actualmente por React Native

### Login

```json
{
  "email": "admin@empresa.com",
  "password": "123456"
}
```

Este flujo es compatible con el endpoint actual de login.

### Registro de empresa

```json
{
  "companyName": "Transportes UTN",
  "cuit": "20123456789",
  "email": "admin@empresa.com",
  "password": "123456",
  "phone": "3511234567",
  "fleetSize": "1-10",
  "termsAccepted": true
}
```

Valores admitidos por `fleetSize`:

- `1-10`
- `11-30`
- `31-100`
- `Más de 100`

El endpoint publico `POST /api/v1/registro` ya crea la empresa y su administrador dentro de una transaccion, registra el tamano de flota y la fecha de aceptacion de terminos, y devuelve un token Sanctum. React Native debe mapear sus nombres camelCase al contrato canonico snake_case.

Contrato canonico de la API:

```json
{
  "razon_social": "Transportes UTN",
  "cuit": "20123456789",
  "email": "admin@empresa.com",
  "password": "123456",
  "telefono": "3511234567",
  "tamano_flota": "1-10",
  "terminos_aceptados": true
}
```

### Recuperar contrasena

```json
{
  "email": "admin@empresa.com"
}
```

### Cambiar contrasena

```json
{
  "password": "nueva123",
  "password_confirmation": "nueva123"
}
```

Este flujo no esta implementado. El frontend tampoco contempla todavia el token de recuperacion.

### Crear chofer

```json
{
  "nombre_completo": "Juan Perez",
  "dni": "12345678",
  "fecha_nacimiento": "1990-05-12",
  "email": "juan@email.com",
  "telefono": "5491112345678",
  "password": "123456"
}
```

Laravel ahora espera `nombre_completo`, `dni`, `fecha_nacimiento` y una `password` obligatoria. `dni` debe ser numerico de 8 digitos.

### Crear entrega

```json
{
  "cliente": "Comercio Centro",
  "destino": "Av. Colon 1234",
  "referencia": "PED-001",
  "observaciones": "Entregar por recepcion",
  "fecha": "2026-06-30",
  "productos": "2 cajas y 1 paquete"
}
```

Laravel actualmente requiere `cliente_id` y `direccion_destino`. El esquema no contiene `fecha_programada` ni `observaciones`, y los detalles de productos se crean mediante un endpoint separado.

### Asignar o desasignar chofer

Asignar:

```json
{
  "chofer_id": "driver-id"
}
```

Desasignar:

```json
{
  "chofer_id": null
}
```

La API permite asignar un UUID, pero no admite `null`. Tambien debe validar que la entrega y el chofer pertenezcan a la empresa del administrador.

### Actualizar estado

```json
{
  "estado": "en camino"
}
```

Estados usados por la aplicacion:

- `pendiente`
- `en camino`
- `realizado`

Laravel utiliza identificadores numericos y los estados internos `pending`, `assigned`, `accepted`, `on_the_way`, `delivered`, `finished` y `cancelled`.

## Respuesta principal esperada por React Native

La pantalla principal espera un objeto con esta estructura general:

```json
{
  "profile": {},
  "company": {},
  "drivers": [],
  "orders": [],
  "admins": []
}
```

Actualmente no existe un endpoint agregado que entregue esta informacion. El endpoint de perfil devuelve solamente `user`, con las relaciones `rol` y `empresa`.

## Comparacion funcional

| Flujo | Estado actual |
| --- | --- |
| Login | Compatible |
| Registro de empresa y administrador | Implementado |
| Recuperacion y cambio de contrasena | No implementado |
| Crear chofer | Contrato incompatible |
| Crear entrega con productos | Contrato incompatible |
| Asignar chofer | Parcial |
| Desasignar chofer | No implementado |
| Actualizar estado | Contrato incompatible |
| Historial automatico de estados | No implementado |
| Auditoria automatica | No implementada |
| Resumen de pantalla principal | No implementado |
| Rol asesor | No implementado |
| Aislamiento de datos por empresa | Incompleto |
| Proteccion global con Sanctum | Incompleta |

## Requisitos academicos y estado real

### Relaciones N a N

Cumplido mediante:

- `detalles_entrega`: entregas con productos.
- `asignaciones_vehiculos`: usuarios choferes con vehiculos.
- `chofer_zonas`: usuarios choferes con zonas de cobertura.

### Relaciones 1 a N

Cumplido. El esquema contiene mas de diez relaciones mediante claves foraneas.

### Maestro-detalle y transacciones

El esquema maestro-detalle existe, pero no hay ningun `DB::transaction()` en el codigo de aplicacion. La creacion atomica de entrega y detalles sigue pendiente.

### Historial de estados

Existen la tabla y el modelo `HistorialEstadoEntrega`, pero los cambios realizados por el chofer no generan registros de historial.

### Auditoria

Existen tabla, modelo, servicio y controlador para `auditoria_logs`. No existen Observers registrados y el servicio conserva un `TODO` para la automatizacion.

### Arquitectura REST

La API responde JSON y el frontend se encuentra separado. Todavia existe la ruta web de bienvenida de Laravel, por lo que no es estrictamente una aplicacion sin vistas.

### Autenticacion y autorizacion

Sanctum, el middleware de roles y las rutas especificas de administrador y chofer estan implementados. Los CRUD generales todavia no tienen proteccion.

## Problemas de aislamiento por empresa

Los siguientes comportamientos deben corregirse:

- El listado de choferes no filtra por `empresa_id`.
- La consulta, actualizacion y eliminacion de choferes no comprueba la empresa.
- El listado y consulta administrativa de entregas no filtra por empresa.
- La asignacion permite seleccionar un chofer de otra empresa.
- Varios servicios CRUD utilizan `all()` o `findOrFail()` sin alcance empresarial.

## Convencion recomendada para la API

Definir un solo contrato y evitar que el backend dependa de multiples alias. Como el cliente HTTP movil todavia no existe, se recomienda acordar ahora:

- Nombres JSON en `snake_case`.
- Respuestas consistentes con `data`, `message` y `errors`.
- Estados canonicos documentados.
- Un adaptador de presentacion en React Native si la interfaz necesita etiquetas diferentes.
- Recursos JSON de Laravel para transformar modelos y relaciones.

## Decisiones pendientes

1. Definir si `realizado` equivale a `delivered` o a `finished`.
2. Definir permisos y casos de uso del rol `asesor`.
3. Definir si `fleetSize` se persiste o se utiliza solo durante el registro.
4. Definir si la contrasena inicial del chofer sera fija, generada o enviada por el administrador.
5. Definir si el registro devuelve inmediatamente un token Sanctum.
6. Definir el contrato definitivo de creacion de productos dentro de una entrega.
7. Definir si se mantiene un endpoint agregado para la pantalla principal o peticiones separadas.

## Roadmap recomendado por commits

1. `feat: implementar registro de empresa y administrador`
2. `feat: agregar recuperacion y cambio de contrasena`
3. `feat: adaptar gestion de choferes al contrato movil`
4. `feat: crear entregas con cliente y productos en una transaccion`
5. `feat: implementar asignacion y desasignacion de choferes`
6. `feat: normalizar estados e historial de entregas`
7. `feat: agregar endpoint de resumen principal`
8. `security: proteger endpoints con Sanctum y roles`
9. `security: aislar datos por empresa`
10. `feat: automatizar auditoria mediante observers`
11. `test: cubrir registro, permisos y reglas de negocio`
12. `docs: documentar contrato final de la API`

## Plan tecnico original y estado

Este plan surgio de la primera revision de arquitectura y debe conservarse junto con el roadmap funcional.

| Numero | Tarea | Estado |
| --- | --- | --- |
| 1 | `refactor: reorganizar controladores bajo Api/V1` | Completada |
| 2 | `refactor: versionar y normalizar todas las rutas` | Completada |
| 3 | `security: proteger endpoints con Sanctum y roles` | Pendiente |
| 4 | `security: restringir recursos segun empresa y usuario` | Pendiente |
| 5 | `refactor: implementar Form Requests para validaciones` | Pendiente |
| 6 | `refactor: normalizar respuestas con API Resources y excepciones` | Pendiente |
| 7 | `feat: agregar paginacion y filtros a los listados` | Pendiente |
| 8 | `test/docs: completar pruebas y documentacion del proyecto` | En progreso |

La tarea 3 debe realizarse despues de definir e implementar el registro de empresa y administrador. De lo contrario, no se puede distinguir correctamente entre rutas publicas de incorporacion y recursos privados de cada empresa.

## Reglas de negocio consolidadas y cumplimiento

| Numero | Regla | Estado actual |
| --- | --- | --- |
| 1 | Una empresa se registra junto con su administrador. | Implementado |
| 2 | El administrador solo gestiona datos de su empresa. | Incompleto |
| 3 | El administrador crea choferes y entregas. | Parcialmente implementado |
| 4 | Una entrega se crea con sus productos en una transaccion. | Pendiente |
| 5 | Solo pueden asignarse choferes pertenecientes a la misma empresa. | Pendiente |
| 6 | El chofer solo accede a sus entregas. | Implementado para las rutas especificas de chofer |
| 7 | Cada cambio de estado crea un historial en la misma transaccion. | Pendiente |
| 8 | Los cambios relevantes generan auditoria automatica. | Pendiente |
| 9 | Salud, login y registro son publicos; el resto requiere Sanctum y rol. | Incompleto |

Estas reglas tienen prioridad sobre los CRUD genericos. Cada endpoint nuevo debe indicar explicitamente que actor puede utilizarlo, a que empresa pertenecen los recursos afectados y si la operacion necesita una transaccion.

## Commits de normalizacion ya realizados

- `cb065c6 refactor: organizar controladores por dominio`
- `e660ca9 refactor: versionar y normalizar rutas de la API`
- `cb0da41 refactor: normalizar tipos JsonResponse`

