# Plan de integracion entre API y aplicacion movil

## Objetivo

Este documento centraliza el estado actual del backend Laravel, el contrato esperado por la aplicacion React Native, las reglas de negocio y las tareas pendientes.

## Flujo principal del sistema

1. Una empresa completa el formulario de registro.
2. El sistema crea la empresa y su usuario administrador en una misma transaccion.
3. El administrador inicia sesion y gestiona exclusivamente los datos de su empresa.
4. El administrador crea choferes y entregas.
5. Para el MVP, cada entrega almacena un unico cliente y un unico producto como campos de texto.
6. El administrador asigna o desasigna una entrega a un chofer de su empresa.
7. El chofer consulta solamente sus entregas asignadas.
8. El chofer acepta una entrega y actualiza su estado.
9. Cada cambio de estado genera un registro de historial dentro de la misma transaccion.
10. Las operaciones relevantes generan registros de auditoria automaticos.

## Actores y permisos

### Administrador

- Pertenece a una empresa.
- Gestiona choferes, administradores, vehiculos, zonas y entregas de su empresa. Para el MVP, cliente y producto se cargan directamente en la entrega.
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
  "producto": "Caja de documentos",
  "direccion_destino": "Av. Colon 1234",
  "referencia": "Entregar por recepcion"
}
```

Este es el contrato MVP implementado y probado. `cliente` y `producto` son campos de texto de la entrega; no requieren UUID ni recursos separados. La empresa se obtiene del administrador autenticado, el estado inicial es `pending` y `referencia` es opcional. El alta no utiliza `cantidad`, `latitud` ni `longitud`.

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

Implementado y probado en el contrato: un UUID asigna o reasigna un chofer de la misma empresa; `null` desasigna, devuelve la entrega a `pending` y limpia `fecha_asignacion`. Las entregas aceptadas o posteriores no pueden modificarse desde este endpoint.

### Actualizar estado

```json
{
  "estado_id": 4
}
```

El chofer usa `PATCH /api/v1/chofer/entregas/{entrega}/state`. Las transiciones permitidas son secuenciales y cada cambio se registra de forma atomica en el historial.

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
| Crear y gestionar choferes | Implementado |
| Crear entrega con cliente y producto | Implementado y probado para el MVP |
| Asignar o reasignar chofer | Implementado |
| Desasignar chofer | Implementado |
| Aceptar y actualizar estado | Implementado y probado |
| Historial automatico de estados | Implementado y probado |
| Auditoria automatica | No implementada |
| Resumen de pantalla principal | No implementado |
| Rol asesor | No implementado |
| Aislamiento de datos por empresa | Incompleto |
| Proteccion global con Sanctum | Incompleta |

## Requisitos academicos y estado real

### Relaciones N a N

Cumplido mediante:

- `detalles_entrega`: relacion historica entre entregas y productos; permanece en el esquema, pero no participa del flujo MVP.
- `asignaciones_vehiculos`: usuarios choferes con vehiculos.
- `chofer_zonas`: usuarios choferes con zonas de cobertura.

### Relaciones 1 a N

Cumplido. El esquema contiene mas de diez relaciones mediante claves foraneas.

### Maestro-detalle y transacciones

El esquema maestro-detalle historico permanece disponible. La regla del MVP lo reemplaza en el flujo activo: una entrega guarda un unico `cliente` y un unico `producto` como texto, por lo que el alta se resuelve con una sola insercion y no requiere una transaccion maestro-detalle.

### Historial de estados

Los cambios de estado por asignacion, desasignacion, aceptacion y avance del chofer generan registros en `historial_estados_entrega` dentro de la misma transaccion que actualiza la entrega. Una reasignacion no crea historial porque mantiene el estado `assigned`. La implementacion incluye estado anterior, estado nuevo, usuario y fecha del cambio.

### Auditoria

Existen tabla, modelo, servicio y controlador para `auditoria_logs`. No existen Observers registrados y el servicio conserva un `TODO` para la automatizacion.

### Arquitectura REST

La API responde JSON y el frontend se encuentra separado. Todavia existe la ruta web de bienvenida de Laravel, por lo que no es estrictamente una aplicacion sin vistas.

### Autenticacion y autorizacion

Sanctum, el middleware de roles y las rutas especificas de administrador y chofer estan implementados. Los CRUD generales todavia no tienen proteccion.

## Problemas de aislamiento por empresa

Los siguientes comportamientos deben corregirse:

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
3. Definir si la contrasena inicial del chofer sera fija, generada o enviada por el administrador.
4. Revisar despues del MVP si cliente y producto vuelven a ser entidades relacionadas.
5. Definir si se mantiene un endpoint agregado para la pantalla principal o peticiones separadas.

## Roadmap de trabajo actualizado

| Numero | Tarea | Estado |
| --- | --- | --- |
| 1 | Registro de empresa y administrador | Completada |
| 2 | Recuperacion y cambio de contrasena | Pendiente |
| 3 | Gestion de choferes adaptada al contrato movil | Completada |
| 4 | Entregas MVP con cliente y producto como campos | Completada y probada |
| 5 | Asignacion, reasignacion y desasignacion de choferes | Completada y probada |
| 6 | Estados canonicos e historial transaccional | Completada y probada |
| 7 | Resumen para la pantalla principal | Pendiente |
| 8 | Proteccion de todos los endpoints con Sanctum y roles | Pendiente |
| 9 | Aislamiento de datos por empresa | En progreso: completo en choferes y entregas |
| 10 | Auditoria automatica mediante Observers | Pendiente |
| 11 | Pruebas de registro, permisos y reglas de negocio | En progreso; bloqueadas localmente por `pdo_sqlite` |
| 12 | Documentacion final del contrato de API | En progreso |

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
| 2 | El administrador solo gestiona datos de su empresa. | Implementado en choferes y entregas; pendiente en CRUD genericos |
| 3 | El administrador crea choferes y entregas. | Implementado |
| 4 | Una entrega guarda un cliente y un producto como campos directos. | Implementado y probado para el MVP |
| 5 | Solo pueden asignarse choferes pertenecientes a la misma empresa. | Implementado |
| 6 | El chofer solo accede a sus entregas. | Implementado para las rutas especificas de chofer |
| 7 | Cada cambio de estado crea un historial en la misma transaccion. | Implementado y probado |
| 8 | Los cambios relevantes generan auditoria automatica. | Pendiente |
| 9 | Salud, login y registro son publicos; el resto requiere Sanctum y rol. | Incompleto |

Estas reglas tienen prioridad sobre los CRUD genericos. Cada endpoint nuevo debe indicar explicitamente que actor puede utilizarlo, a que empresa pertenecen los recursos afectados y si la operacion necesita una transaccion.

## Pendientes priorizados

### Prioridad inmediata

1. Agregar filtros y paginacion a `GET /api/v1/admin/entregas`:
   - `estado_id`.
   - `chofer_id`.
   - entregas sin chofer.
   - paginacion conservando el alcance por empresa.
2. Proteger o retirar los CRUD genericos que todavia estan fuera de los grupos Sanctum.
3. Completar el aislamiento por empresa en los servicios genericos.

### Prioridad posterior

1. Implementar recuperacion y cambio de contrasena.
2. Definir e implementar el resumen de la pantalla principal.
3. Automatizar auditoria con Observers.
4. Incorporar Form Requests y API Resources.
5. Definir el rol asesor y el significado final de `realizado`.

## Estado al cierre del 1 de julio de 2026

Funciona y fue probado manualmente:

- Registro de empresa y administrador con token Sanctum.
- CRUD administrativo de choferes restringido por empresa.
- Alta de entrega MVP con `cliente`, `producto`, `direccion_destino` y `referencia` opcional.
- Empresa obtenida del administrador autenticado y estado inicial `pending`.
- Asignacion, reasignacion y desasignacion de choferes de la misma empresa.
- Aceptacion y avance secuencial `assigned -> accepted -> on_the_way -> delivered -> finished`.
- Historial atomico para asignacion, desasignacion, aceptacion y cambios posteriores.
- Detalle de entrega con estado, usuario e historial de transiciones.
- Catalogo canonico de estados garantizado mediante migracion.

Limitaciones actuales:

- Los CRUD genericos definidos fuera de los grupos protegidos todavia necesitan revision de autenticacion y alcance empresarial.
- No existen recuperacion de contrasena, resumen principal ni auditoria automatica.
- Las pruebas Feature no se pueden ejecutar en este entorno porque PHP no tiene habilitado `pdo_sqlite`; la sintaxis, Pint y las pruebas manuales con Insomnia si fueron validadas.

## Proxima sesion recomendada

Implementar filtros y paginacion para `GET /api/v1/admin/entregas`, alineados con la pantalla de Figma:

1. Validar los parametros de consulta.
2. Filtrar por `estado_id` y `chofer_id`.
3. Permitir consultar entregas sin chofer.
4. Paginar resultados sin perder el filtro por `empresa_id`.
5. Agregar pruebas Feature.
6. Probar manualmente con Insomnia y actualizar este documento.

No volver a agregar `fecha_programada`, `cantidad`, `latitud` ni `longitud` a la entrega MVP sin una nueva decision de negocio.

## Commits relevantes

- `cb065c6 refactor: organizar controladores por dominio`
- `e660ca9 refactor: versionar y normalizar rutas de la API`
- `cb0da41 refactor: normalizar tipos JsonResponse`
- `7a23163 feat: simplificar entregas para el MVP`
- `9431c75 feat: asegurar catalogo de estados de entrega`
- `f05d134 fix: hacer idempotente el seeder de usuarios`
- `cb54256 feat: completar asignacion de choferes`
- `ba7bc72 feat: registrar historial de estados de entrega`
