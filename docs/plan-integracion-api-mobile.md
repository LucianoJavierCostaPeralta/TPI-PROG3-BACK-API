# Plan de integracion entre API y aplicacion movil

## Objetivo

Este documento centraliza el estado actual del backend Laravel, el contrato esperado por la aplicacion React Native, las reglas de negocio y las tareas pendientes.

## Flujo principal del sistema

1. Una empresa completa el formulario de registro.
2. El sistema crea la empresa y su usuario administrador en una misma transaccion.
3. El administrador inicia sesion y gestiona exclusivamente los datos de su empresa.
4. El administrador crea choferes y entregas.
5. Para el MVP, cada entrega almacena el nombre y DNI del cliente, y un unico producto como campos directos.
6. El administrador asigna o desasigna una entrega a un chofer de su empresa.
7. El chofer consulta solamente sus entregas asignadas.
8. El chofer acepta una entrega y actualiza su estado.
9. Cada cambio de estado genera un registro de historial dentro de la misma transaccion.
10. Las operaciones relevantes generan registros de auditoria automaticos.

## Actores y permisos

### Administrador

- Pertenece a una empresa.
- Gestiona choferes, administradores, vehiculos, zonas y entregas de su empresa. Para el MVP, nombre y DNI del cliente, y producto se cargan directamente en la entrega.
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
  "cliente_dni": "30123456",
  "producto": "Caja de documentos",
  "direccion_destino": "Av. Colon 1234",
  "referencia": "Entregar por recepcion"
}
```

Este es el contrato MVP implementado y probado. `cliente`, `cliente_dni` y `producto` son campos directos de la entrega; el DNI contiene exactamente 8 digitos y se usa para verificar la recepcion. `cliente` y `producto` son campos de texto de la entrega; no requieren UUID ni recursos separados. La empresa se obtiene del administrador autenticado, el estado inicial es `pending` y `referencia` es opcional. El alta no utiliza `cantidad`, `latitud` ni `longitud`.

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

Cambio ordinario:

```json
{
  "estado_id": 4
}
```

Para pasar de `on_the_way` a `delivered`, el chofer debe ingresar el DNI del cliente:

```json
{
  "estado_id": 5,
  "cliente_dni": "30123456"
}
```

El chofer usa `PATCH /api/v1/chofer/entregas/{entrega}/state`. Las transiciones permitidas son secuenciales y cada cambio se registra de forma atomica en el historial. El paso a `delivered` requiere que `cliente_dni` coincida; el DNI esperado no se expone en las respuestas del chofer.

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
| Crear entrega con nombre y DNI del cliente, y producto | Implementado y probado para el MVP |
| Asignar o reasignar chofer | Implementado |
| Desasignar chofer | Implementado |
| Aceptar y actualizar estado | Implementado y probado |
| Historial automatico de estados | Implementado y probado |
| Auditoria automatica | No implementada |
| Resumen de pantalla principal | No implementado |
| Rol asesor | No implementado |
| Aislamiento de datos por empresa | Completo en los recursos del MVP |
| Proteccion global con Sanctum | Completa; CRUD heredados restringidos a administradores |

## Requisitos academicos y estado real

### Relaciones N a N

Cumplido mediante:

- `detalles_entrega`: relacion historica entre entregas y productos; permanece en el esquema, pero no participa del flujo MVP.
- `asignaciones_vehiculos`: usuarios choferes con vehiculos.
- `chofer_zonas`: usuarios choferes con zonas de cobertura.

### Relaciones 1 a N

Cumplido. El esquema contiene mas de diez relaciones mediante claves foraneas.

### Maestro-detalle y transacciones

El esquema maestro-detalle historico permanece disponible. La regla del MVP lo reemplaza en el flujo activo: una entrega guarda `cliente`, `cliente_dni` y `producto` como campos directos, por lo que el alta se resuelve con una sola insercion y no requiere una transaccion maestro-detalle.

### Historial de estados

Los cambios de estado por asignacion, desasignacion, aceptacion y avance del chofer generan registros en `historial_estados_entrega` dentro de la misma transaccion que actualiza la entrega. Una reasignacion no crea historial porque mantiene el estado `assigned`. La implementacion incluye estado anterior, estado nuevo, usuario y fecha del cambio.

### Auditoria

Existen tabla, modelo, servicio y controlador para `auditoria_logs`. No existen Observers registrados y el servicio conserva un `TODO` para la automatizacion.

### Arquitectura REST

La API responde JSON y el frontend se encuentra separado. Todavia existe la ruta web de bienvenida de Laravel, por lo que no es estrictamente una aplicacion sin vistas.

### Autenticacion y autorizacion

Sanctum, el middleware de roles y las rutas especificas de administrador y chofer estan implementados. Los CRUD generales requieren token Sanctum y rol administrador.

## Aislamiento por empresa

Los recursos activos del MVP derivan la empresa del usuario autenticado. Empresas, administradores, choferes y entregas rechazan accesos cruzados. Los servicios heredados que conservan consultas globales no estan expuestos sin autenticacion y rol administrador; su refactor interno queda fuera del cierre obligatorio.

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
| 4 | Entregas MVP con cliente, cliente_dni y producto como campos | Completada y probada |
| 5 | Asignacion, reasignacion y desasignacion de choferes | Completada y probada |
| 6 | Estados canonicos e historial transaccional | Completada y probada |
| 7 | Resumen para la pantalla principal | Pendiente |
| 8 | Proteccion de todos los endpoints con Sanctum y roles | Completada |
| 9 | Aislamiento de datos por empresa | Completada en recursos MVP |
| 10 | Auditoria automatica mediante Observers | Pendiente |
| 11 | Pruebas de registro, permisos y reglas de negocio | Preparadas; ejecucion con BD bloqueada por `pdo_sqlite` |
| 12 | Documentacion final del contrato de API | Completada para el MVP |

## Plan tecnico original y estado

Este plan surgio de la primera revision de arquitectura y debe conservarse junto con el roadmap funcional.

| Numero | Tarea | Estado |
| --- | --- | --- |
| 1 | `refactor: reorganizar controladores bajo Api/V1` | Completada |
| 2 | `refactor: versionar y normalizar todas las rutas` | Completada |
| 3 | `security: proteger endpoints con Sanctum y roles` | Completada |
| 4 | `security: restringir recursos segun empresa y usuario` | Completada para el MVP |
| 5 | `refactor: implementar Form Requests para validaciones` | Pendiente |
| 6 | `refactor: normalizar respuestas con API Resources y excepciones` | Pendiente |
| 7 | `feat: agregar paginacion y filtros a los listados` | Completada en entregas administrativas |
| 8 | `test/docs: completar pruebas y documentacion del proyecto` | Completada para el MVP |

## Reglas de negocio consolidadas y cumplimiento

| Numero | Regla | Estado actual |
| --- | --- | --- |
| 1 | Una empresa se registra junto con su administrador. | Implementado |
| 2 | El administrador solo gestiona datos de su empresa. | Implementado en los recursos del MVP |
| 3 | El administrador crea choferes y entregas. | Implementado |
| 4 | Una entrega guarda nombre y DNI del cliente, y producto como campos directos. | Implementado y probado para el MVP |
| 5 | Solo pueden asignarse choferes pertenecientes a la misma empresa. | Implementado |
| 6 | El chofer solo accede a sus entregas. | Implementado para las rutas especificas de chofer |
| 7 | Cada cambio de estado crea un historial en la misma transaccion. | Implementado y probado |
| 8 | Los cambios relevantes generan auditoria automatica. | Pendiente |
| 9 | Salud, login y registro son publicos; el resto requiere Sanctum y rol. | Implementado |

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
- Alta de entrega MVP con `cliente`, `cliente_dni`, `producto`, `direccion_destino` y `referencia` opcional.
- Empresa obtenida del administrador autenticado y estado inicial `pending`.
- Asignacion, reasignacion y desasignacion de choferes de la misma empresa.
- Aceptacion y avance secuencial `assigned -> accepted -> on_the_way -> delivered -> finished`.
- Historial atomico para asignacion, desasignacion, aceptacion y cambios posteriores.
- Detalle de entrega con estado, usuario e historial de transiciones.
- Catalogo canonico de estados garantizado mediante migracion.

Limitaciones actuales:

- Los CRUD heredados estan protegidos para administradores; sus servicios internos aun conservan deuda de refactor fuera del alcance MVP.
- No existen recuperacion de contrasena, resumen principal ni auditoria automatica.
- La suite con base no se puede ejecutar en este entorno porque PHP no tiene habilitado `pdo_sqlite`; 21 pruebas sin base pasan, toda la sintaxis es valida y los archivos modificados pasan Pint.

## Plan ejecutado para el cierre del MVP: 1 de julio de 2026 al mediodia

El objetivo fue entregar un MVP funcional y defendible. Los cinco frentes se ejecutaron en este orden:

1. Seguridad de rutas:
   - proteger o retirar CRUD genericos expuestos;
   - verificar middleware Sanctum y roles.
2. Aislamiento por empresa:
   - impedir lecturas o cambios entre empresas;
   - priorizar recursos utilizados por el MVP.
3. Listado de entregas:
   - filtros por estado y chofer;
   - entregas sin chofer;
   - paginacion conservando el alcance empresarial.
4. Validacion integral:
   - probar registro, login, choferes y ciclo completo de entrega con Insomnia;
   - corregir errores bloqueantes;
   - verificar migraciones desde una base limpia si el entorno lo permite.
5. Cierre:
   - actualizar este documento;
   - revisar el estado de Git;
   - crear commits pequenos y hacer push final a `dev`.

Solo si queda margen despues de cerrar esos cinco frentes:

- resumen de pantalla principal;
- recuperacion de contrasena;
- auditoria automatica.

Fuera del alcance obligatorio de esta entrega:

- rol asesor;
- migracion general a Form Requests y API Resources;
- volver a modelar cliente y producto como entidades;
- cualquier cambio que reintroduzca fecha programada, cantidad o coordenadas en la entrega MVP.

Filtros y paginacion implementados para `GET /api/v1/admin/entregas`, alineados con la pantalla de Figma:

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

## Resultado del cierre del MVP: 1 de julio de 2026

Los cinco frentes obligatorios quedaron ejecutados:

1. Todos los CRUD heredados bajo `/api/v1` requieren `auth:sanctum` y rol `admin`. Salud, login y registro permanecen publicos.
2. Empresa, administradores, choferes y entregas se consultan y modifican con alcance de la empresa autenticada. Los IDs pertenecientes a otra empresa devuelven `404`; la asignacion de un chofer ajeno devuelve `422`.
3. `GET /api/v1/admin/entregas` pagina 15 registros por defecto y acepta:
   - `estado_id`: ID valido del catalogo de estados;
   - `chofer_id`: UUID de un chofer de la empresa autenticada;
   - `sin_chofer=1`: solamente entregas sin asignacion;
   - `per_page`: entre 1 y 100.
   La respuesta paginada se encuentra en `data` y sus registros en `data.data`. `chofer_id` y `sin_chofer=1` no se pueden combinar.
4. La validacion final obtuvo los siguientes resultados:
   - sintaxis valida en todos los archivos PHP de `app`, `routes`, `database` y `tests`;
   - 100 rutas API registradas correctamente;
   - 56 pruebas descubiertas: 21 sin base de datos pasan y 35 quedan bloqueadas antes de ejecutar porque falta `pdo_sqlite`;
   - Pint pasa en todos los archivos modificados. La ejecucion global detecta deuda de formato preexistente en modelos, servicios y migraciones heredados, fuera del alcance funcional del cierre;
   - la secuencia manual reproducible quedo documentada en `docs/pruebas-manuales-insomnia-mvp.md`.
5. El cierre conserva el contrato MVP: `cliente`, `cliente_dni` y `producto` son campos directos de `Entrega`; no se incorporaron `fecha_programada`, `cantidad`, `latitud` ni `longitud`.

Para ejecutar toda la suite automatica queda como requisito de entorno instalar o habilitar `pdo_sqlite`. Como alternativa inmediata, ejecutar la guia de Insomnia contra una base MySQL de prueba migrada desde cero.
