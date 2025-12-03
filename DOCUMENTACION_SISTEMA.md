# Documentación del Sistema - AppCentroMedico

## 1. DESCRIPCIÓN GENERAL

**AppCentroMedico** es un sistema de gestión integral para centros médicos desarrollado con Laravel 10.x, Inertia.js y Vue 3. El sistema permite la administración completa de prestaciones médicas, planes de salud, órdenes de atención, rendiciones a prestadores y gestión de afiliados.

### Tecnologías Principales
- **Backend**: Laravel 10.x + PHP 8.2
- **Frontend**: Vue 3 (Composition API) + Inertia.js
- **Base de Datos**: PostgreSQL (principal) + MySQL + SQL Server (externa)
- **Estilos**: Tailwind CSS
- **PDFs**: DomPDF

---

## 2. MÓDULOS PRINCIPALES

### 2.1 GESTIÓN DE PLANES

**Descripción**: Administración de planes de salud con cobertura de servicios médicos.

**Funcionalidades**:
- Crear, editar y listar planes de salud
- Campos principales:
  - Nombre del plan
  - Código corto (único, no modificable)
  - Vigencia (desde/hasta)
  - Estado (activo, inactivo, suspendido)
  - Porcentajes: salud, odontología, orden
  - Descripción
- Asignación de planes a sucursales (relación many-to-many)
- Relación con prestaciones a través de tabla intermedia

**Modelos**: `Plan`, `Sucursal`, `PlanSucursal` (pivot)

**Rutas**:
- `GET /planes` - Lista de planes
- `GET /planes/create` - Formulario creación
- `POST /planes` - Guardar nuevo plan
- `GET /planes/{id}/edit` - Formulario edición
- `PUT /planes/{id}` - Actualizar plan

**Tablas BD**:
- `planes`
- `plan_sucursal` (pivot)
- `prestaciones_planes` (relación con prestaciones)

---

### 2.2 GESTIÓN DE PRESTACIONES

**Descripción**: Catálogo completo de servicios médicos ofrecidos por el centro.

**Funcionalidades**:
- CRUD completo de prestaciones
- Campos principales:
  - Código (6 dígitos, único)
  - Nombre y descripción
  - Rubro (clasificación)
  - Precio general
  - Precio afiliado
  - Valor IPS (Instituto de Previsión Social)
  - Estado
  - Observaciones
- Migración masiva desde CSV
- Asignación automática a planes
- Búsqueda y filtrado avanzado

**Modelos**: `Prestacion`, `Rubro`, `Plan`

**Rutas**:
- `GET /prestaciones` - Lista de prestaciones
- `GET /prestaciones/create` - Formulario creación
- `POST /prestaciones` - Guardar prestación
- `GET /prestaciones/{id}` - Ver detalle
- `GET /prestaciones/{id}/edit` - Formulario edición
- `PUT /prestaciones/{id}` - Actualizar
- `DELETE /prestaciones/{id}` - Eliminar

**Comando Artisan**:
```bash
php artisan prestaciones:migrar-csv {archivo.csv}
```
**Formato CSV**: `ID_RUBRO;CODIGO;NOMBRE;VALOR_IPS`

**Tablas BD**:
- `prestaciones`
- `rubros`
- `prestaciones_planes` (relación con planes)

---

### 2.3 GESTIÓN DE ÓRDENES

**Descripción**: Generación y seguimiento de órdenes de atención médica.

**Funcionalidades**:
- Crear órdenes con múltiples prestaciones
- Buscar afiliados desde base de datos externa (SQL Server)
- Sincronización automática de afiliados
- Cálculo automático de totales (prestador/afiliado)
- Numeración automática por sucursal
- Estados: Pendiente, Pagada, Anulada
- Anulación con generación de Nota de Crédito
- Generación de PDF en formato A5 horizontal

**Modelos**: `Orden`, `DetalleOrden`, `Afiliado`, `Prestador`, `TipoComprobante`

**Rutas**:
- `GET /ordenes` - Lista de órdenes
- `GET /ordenes/create` - Formulario creación
- `POST /ordenes` - Guardar orden
- `GET /ordenes/{id}` - Ver detalle
- `DELETE /ordenes/{id}` - Anular orden
- `GET /ordenes/{id}/pdf` - Generar PDF

**APIs**:
- `POST /api/ordenes/buscar-afiliados` - Buscar afiliados por certificado (SQL Server)
- `POST /api/ordenes/obtener-precio` - Obtener precio de prestación según plan

**Tablas BD**:
- `ordenes`
- `det_ordenes`
- `afiliados` (sincronizados desde SQL Server)
- `tipo_comprobante`

---

### 2.4 GESTIÓN DE RENDICIONES

**Descripción**: Control de rendiciones a prestadores médicos.

**Funcionalidades**:
- Crear rendiciones por prestador y sucursal
- Agregar órdenes a rendición (escáner de código de barras)
- Validación de órdenes (estado, duplicados)
- Cálculo automático de totales
- Cerrar rendiciones (bloqueo de modificaciones)
- Generación de PDF con detalle completo
- Estados: Abierta, Cerrada

**Modelos**: `Rendicion`, `DetalleRendicion`, `Orden`, `Prestador`

**Rutas**:
- `GET /rendiciones` - Lista de rendiciones
- `GET /rendiciones/create` - Formulario creación
- `POST /rendiciones` - Guardar rendición
- `GET /rendiciones/{id}` - Ver detalle
- `POST /rendiciones/{id}/agregar-orden` - Agregar orden
- `POST /rendiciones/{id}/quitar-orden` - Quitar orden
- `POST /rendiciones/{id}/cerrar` - Cerrar rendición
- `GET /rendiciones/{id}/pdf` - Generar PDF

**Tablas BD**:
- `rendiciones`
- `detalle_rendiciones`

---

### 2.5 GESTIÓN DE SUCURSALES

**Descripción**: Administración de las distintas sedes del centro médico.

**Funcionalidades**:
- CRUD completo de sucursales
- Campos: nombre, código, dirección, teléfono, email, responsable, estado
- Asignación de planes a sucursales
- Relación con usuarios y órdenes

**Modelos**: `Sucursal`, `Plan`, `User`

**Rutas**:
- `GET /sucursales` - Lista de sucursales
- `GET /sucursales/create` - Formulario creación
- `POST /sucursales` - Guardar sucursal
- `GET /sucursales/{id}/edit` - Formulario edición
- `PUT /sucursales/{id}` - Actualizar

**Tablas BD**:
- `sucursales`
- `plan_sucursal` (relación con planes)

---

### 2.6 GESTIÓN DE AFILIADOS

**Descripción**: Registro de afiliados y beneficiarios del centro médico.

**Funcionalidades**:
- Sincronización automática desde SQL Server
- Búsqueda por certificado
- Datos: DNI, nombre, apellido, certificado, vínculo, plan
- Estados: activo, inactivo

**Modelos**: `Afiliado`, `BeneficiarioExterno` (SQL Server)

**Conexión Externa**:
- Base de datos: SQL Server (PieveSalud)
- Tabla: `dbo.Beneficiarios`
- Sincronización en tiempo real al buscar órdenes

**Tablas BD**:
- `afiliados` (local PostgreSQL)
- `dbo.Beneficiarios` (SQL Server externa)

---

### 2.7 GESTIÓN DE PRESTADORES

**Descripción**: Registro de profesionales médicos.

**Funcionalidades**:
- CRUD completo de prestadores
- Campos: nombre, apellido, documento, matrícula, especialidad, contacto
- Estados: activo, inactivo, suspendido
- Relación con órdenes y rendiciones

**Modelos**: `Prestador`

**Tablas BD**:
- `prestadores`

---

### 2.8 GESTIÓN DE RUBROS

**Descripción**: Clasificación de prestaciones médicas.

**Funcionalidades**:
- CRUD completo de rubros
- Campos: código, nombre, descripción, estado
- Categorías: Consultas, Laboratorio, Imagenología, Odontología, etc.

**Modelos**: `Rubro`

**Tablas BD**:
- `rubros`

---

### 2.9 MAPEO DE COBERTURAS (SQL Server)

**Descripción**: Integración con base de datos externa para mapeo de coberturas.

**Funcionalidades**:
- Lectura de datos desde SQL Server
- Mapeo de planes y coberturas
- Generación de tabla local `mapeo_planes_coberturas`
- Consultas combinadas entre bases de datos

**Comando Artisan**:
```bash
php artisan mapeo:generar
```

**Ruta Web**:
- `GET|POST /mapeo/generar` - Generar mapeo

**Tablas**:
- `mapeo_planes_coberturas` (local PostgreSQL)
- `dbo.PlanesFichas`, `dbo.Beneficiarios` (SQL Server)

---

## 3. ARQUITECTURA DE BASE DE DATOS

### 3.1 Conexiones Configuradas

**PostgreSQL (principal)**:
- Host: localhost
- Puerto: 5432
- Base de datos: `appcentromedico`
- Uso: Gestión principal del sistema

**MySQL (secundaria)**:
- Host: localhost
- Puerto: 3306
- Base de datos: `appcm`
- Uso: Datos específicos y migraciones

**SQL Server (externa)**:
- Conexión: `sqlsrv_externa`
- Base de datos: `PieveSalud`
- Uso: Consulta de afiliados y mapeo de coberturas

### 3.2 Relaciones Principales

```
planes
  ├── plan_sucursal (pivot) → sucursales
  └── prestaciones_planes (pivot) → prestaciones

prestaciones
  ├── prestaciones_planes → planes
  ├── det_ordenes → ordenes
  └── rubros

ordenes
  ├── det_ordenes → prestaciones
  ├── detalle_rendiciones → rendiciones
  ├── afiliados (titular_id, beneficiario_id)
  ├── prestadores
  ├── tipo_comprobante
  └── sucursales

rendiciones
  ├── detalle_rendiciones → ordenes
  ├── prestadores
  └── sucursales
```

### 3.3 Tablas Principales

| Tabla | Descripción | Registros Típicos |
|-------|-------------|-------------------|
| planes | Planes de salud | ~20 |
| prestaciones | Catálogo de servicios | ~443 |
| rubros | Clasificación de prestaciones | ~18 |
| sucursales | Sedes del centro | ~5 |
| afiliados | Beneficiarios | Miles |
| prestadores | Profesionales médicos | ~50 |
| ordenes | Órdenes de atención | Miles |
| det_ordenes | Detalle de órdenes | Miles |
| rendiciones | Rendiciones a prestadores | Cientos |
| detalle_rendiciones | Detalle de rendiciones | Miles |

---

## 4. FLUJOS DE TRABAJO PRINCIPALES

### 4.1 Flujo de Creación de Orden

1. Usuario accede a `/ordenes/create`
2. Ingresa certificado del afiliado
3. Sistema busca en SQL Server
4. Sincroniza afiliado en base local
5. Usuario selecciona prestaciones
6. Sistema calcula precios según plan
7. Usuario guarda orden
8. Sistema genera número de orden automático
9. Orden queda en estado "Pendiente"

### 4.2 Flujo de Rendición a Prestador

1. Usuario crea rendición (`/rendiciones/create`)
2. Selecciona prestador y sucursal
3. Sistema muestra formulario para agregar órdenes
4. Usuario escanea/ingresa número de orden
5. Sistema valida:
   - Orden existe
   - Pertenece al prestador
   - No está anulada
   - No está en otra rendición
6. Si válida, agrega a la rendición
7. Repite hasta completar todas las órdenes
8. Usuario cierra rendición
9. Sistema genera PDF automáticamente
10. Rendición queda bloqueada (no se puede modificar)

### 4.3 Flujo de Anulación de Orden

**Si orden está PENDIENTE (estado 1)**:
1. Usuario solicita anulación
2. Ingresa motivo
3. Sistema marca orden como "Anulada"
4. Fin del proceso

**Si orden está PAGADA (estado 2)**:
1. Usuario solicita anulación
2. Ingresa motivo
3. Sistema genera Nota de Crédito automática
4. Copia todos los detalles de la orden original
5. Marca orden original como "Anulada"
6. Muestra la Nota de Crédito generada

### 4.4 Flujo de Migración de Prestaciones

1. Preparar archivo CSV con formato: `ID_RUBRO;CODIGO;NOMBRE;VALOR_IPS`
2. Colocar archivo en `storage/app/`
3. Listar rubros disponibles para conocer IDs
4. Ejecutar comando:
   ```bash
   php artisan prestaciones:migrar-csv storage/app/prestaciones.csv
   ```
5. Sistema valida cada línea:
   - ID de rubro existe
   - Código es válido (6 dígitos)
   - No hay duplicados
6. Crea prestaciones
7. Asigna automáticamente al Plan ID=1
8. Muestra reporte de resultados

---

## 5. DISEÑO VISUAL

### 5.1 Estilo Corporativo

**Paleta de Colores**:
- Principal: `#2D6660` (verde oscuro)
- Secundario: Grises (bordes y textos)
- Acentos: Azul, Verde, Rojo (acciones y estados)
- Fondos: Blanco y gris claro

**Tipografía**:
- Font: Arial, sans-serif
- Tamaños: 9px-16px según contexto

### 5.2 Componentes UI

**Cards**:
```vue
<div class="rounded-lg shadow-sm border border-gray-600 p-6"
     style="background-color: #2D6660;">
    <h2 class="text-lg font-semibold text-white">Título</h2>
    <!-- contenido -->
</div>
```

**Tablas**:
- Header: `bg-gray-700` con texto `text-gray-200`
- Body: `bg-white` con bordes `divide-gray-200`
- Responsive con `overflow-x-auto`

**Botones**:
- Primario: `bg-blue-600 hover:bg-blue-700`
- Éxito: `bg-green-600 hover:bg-green-700`
- Peligro: `bg-red-600 hover:bg-red-700`
- Secundario: `bg-gray-300 hover:bg-gray-400`

**Badges de Estado**:
- Activo/Abierto: `bg-green-100 text-green-800`
- Pendiente: `bg-yellow-100 text-yellow-800`
- Cerrado: `bg-blue-100 text-blue-800`
- Anulado/Inactivo: `bg-red-100 text-red-800`

---

## 6. FUNCIONALIDADES PENDIENTES

### 6.1 Alta Prioridad

- [ ] Actualizar vistas de **Órdenes** con estilo de rendiciones
- [ ] Actualizar vistas de **Planes** con estilo de rendiciones
- [ ] Actualizar vistas de **Prestaciones** con estilo de rendiciones
- [ ] Actualizar vistas de **Sucursales** con estilo de rendiciones
- [ ] Implementar búsqueda avanzada en todas las vistas principales
- [ ] Agregar filtros por fecha en listados
- [ ] Exportación a Excel de reportes

### 6.2 Media Prioridad

- [ ] Dashboard con estadísticas principales
- [ ] Reportes de rendiciones por período
- [ ] Reportes de prestaciones más utilizadas
- [ ] Gráficos de actividad por prestador
- [ ] Sistema de notificaciones
- [ ] Logs de auditoría completos

### 6.3 Baja Prioridad

- [ ] Módulo de facturación
- [ ] Integración con sistemas de pago
- [ ] App móvil para prestadores
- [ ] Portal de autogestión para afiliados
- [ ] Sistema de turnos online
- [ ] Historias clínicas digitales

---

## 7. COMANDOS ÚTILES

### 7.1 Artisan Commands

```bash
# Migración de prestaciones desde CSV
php artisan prestaciones:migrar-csv storage/app/prestaciones.csv

# Generar mapeo de coberturas desde SQL Server
php artisan mapeo:generar

# Limpiar cachés
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Ejecutar migraciones
php artisan migrate

# Ver estado de migraciones
php artisan migrate:status
```

### 7.2 Base de Datos (Tinker)

```bash
# Listar rubros disponibles
php artisan tinker --execute="
\$rubros = DB::table('rubros')->select('id', 'nombre')->orderBy('id')->get();
foreach (\$rubros as \$r) { echo \"ID: \$r->id - \$r->nombre\n\"; }
"

# Limpiar prestaciones y relaciones
php artisan tinker --execute="
DB::beginTransaction();
DB::table('prestaciones_planes')->delete();
DB::table('prestaciones')->delete();
DB::commit();
"

# Contar prestaciones por rubro
php artisan tinker --execute="
\$rubros = DB::table('prestaciones')
    ->join('rubros', 'prestaciones.rubro_id', '=', 'rubros.id')
    ->select('rubros.nombre', DB::raw('count(*) as total'))
    ->groupBy('rubros.nombre')
    ->get();
foreach (\$rubros as \$r) { echo \"\$r->nombre: \$r->total\n\"; }
"
```

---

## 8. CONFIGURACIÓN DEL ENTORNO

### 8.1 Requisitos

- PHP 8.2+
- PostgreSQL 12+
- MySQL 5.7+ (opcional)
- SQL Server (conexión externa)
- Composer
- Node.js 18+ y NPM

### 8.2 Variables de Entorno (.env)

```env
APP_NAME="AppCentroMedico"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8001

# PostgreSQL (principal)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=appcentromedico
DB_USERNAME=postgres
DB_PASSWORD=

# MySQL (secundaria)
MYSQL_CONNECTION=mysql
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_DATABASE=appcm
MYSQL_USERNAME=root
MYSQL_PASSWORD=

# SQL Server (externa)
SQLSRV_HOST=
SQLSRV_PORT=1433
SQLSRV_DATABASE=PieveSalud
SQLSRV_USERNAME=
SQLSRV_PASSWORD=
```

### 8.3 Instalación

```bash
# Clonar repositorio
git clone [url]

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Copiar archivo de entorno
cp .env.example .env

# Generar key de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Compilar assets
npm run dev

# Iniciar servidor
php artisan serve --port=8001
```

---

## 9. MANTENIMIENTO Y SOPORTE

### 9.1 Backups Recomendados

**Bases de Datos**:
- PostgreSQL: Backup diario automático
- Archivos adjuntos: Backup semanal

**Script de Backup PostgreSQL**:
```bash
#!/bin/bash
pg_dump -U postgres appcentromedico > backup_$(date +%Y%m%d).sql
```

### 9.2 Logs del Sistema

**Ubicación**: `storage/logs/laravel.log`

**Niveles de Log**:
- `emergency`: Sistema no funcional
- `alert`: Acción inmediata requerida
- `critical`: Condiciones críticas
- `error`: Errores que no detienen la app
- `warning`: Advertencias
- `notice`: Eventos normales pero significativos
- `info`: Información general
- `debug`: Información de debug

### 9.3 Monitoreo

**Métricas a Vigilar**:
- Tiempo de respuesta de páginas
- Uso de memoria PHP
- Conexiones a base de datos
- Espacio en disco
- Logs de errores

---

## 10. CONTACTO Y DOCUMENTACIÓN ADICIONAL

**Desarrollador**: Claude Code Assistant
**Fecha de Creación**: Octubre 2025
**Versión del Sistema**: 1.0

**Archivos de Documentación**:
- `README.md` - Guía rápida de instalación
- `MIGRACION_PRESTACIONES.md` - Guía detallada de migración de prestaciones
- `DOCUMENTACION_SISTEMA.md` - Este archivo (documentación completa)

**Ubicación del Código**:
- Backend: `app/Http/Controllers/`, `app/Models/`
- Frontend: `resources/js/Pages/`
- Rutas: `routes/web.php`
- Migraciones: `database/migrations/`
- PDFs: `resources/views/pdf/`

---

## NOTAS FINALES

Este sistema está en constante evolución. Se recomienda mantener esta documentación actualizada con cada cambio significativo en la funcionalidad o estructura del sistema.

Para preguntas o soporte técnico, consultar los archivos de documentación específicos o revisar el código fuente con comentarios inline.

**Última actualización**: Octubre 2025
