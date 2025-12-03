# Migración de Prestaciones desde CSV

## Descripción
Script para migrar prestaciones masivamente desde un archivo CSV al sistema.

## Formato del Archivo CSV

El archivo debe tener el siguiente formato (separado por punto y coma `;`):

```
ID_RUBRO;CODIGO;NOMBRE;VALOR_IPS
```

### Campos:
1. **ID_RUBRO**: ID numérico del rubro (debe existir en la tabla `rubros`)
2. **CODIGO**: Código de 6 dígitos numéricos (debe ser único)
3. **NOMBRE**: Nombre descriptivo de la prestación
4. **VALOR_IPS**: Valor para Instituto de Previsión Social (número decimal)

### Ejemplo:
```
1;100001;Consulta Médica General;1500.00
1;100002;Consulta Especializada Cardiología;2500.00
3;200001;Hemograma Completo;800.00
3;200002;Glucemia en Ayunas;600.00
2;300001;Radiografía de Tórax;1200.00
8;400001;Limpieza Dental;1000.00
```

## Uso del Comando

### Sintaxis:
```bash
php artisan prestaciones:migrar-csv {ruta_al_archivo}
```

### Ejemplos:

1. **Migrar desde archivo en storage:**
```bash
php artisan prestaciones:migrar-csv storage/app/prestaciones.csv
```

2. **Migrar desde ruta absoluta:**
```bash
php artisan prestaciones:migrar-csv D:/datos/prestaciones.csv
```

3. **Usando el archivo de ejemplo:**
```bash
php artisan prestaciones:migrar-csv storage/app/prestaciones_ejemplo.csv
```

## Características del Script

### ✅ Funcionalidades:
- Crea prestaciones masivamente desde CSV
- Valida que los IDs de rubro existan en la base de datos
- Asigna todas las prestaciones al **Plan ID=1**
- Todas las prestaciones se crean con estado **activo**
- Validación de formato de código (6 dígitos)
- Validación de campos requeridos
- Manejo de errores por línea
- Transacciones SQL (todo o nada)
- Reporte detallado de resultados

### 📊 Campos creados automáticamente:
- `codigo`: Del CSV
- `nombre`: Del CSV
- `rubro_id`: Busca o crea el rubro
- `precio_general`: 0 (por defecto)
- `valor_ips`: Del CSV
- `estado`: activo
- `descripcion`: Igual al nombre

### 🔗 Relación con Plan:
- `plan_id`: 1 (fijo)
- `valor_afiliado`: 0 (por defecto)
- `valor_particular`: 0 (por defecto)
- `estado`: activo
- `fecha_desde`: Fecha actual

## Preparación Antes de Migrar

### 1. Listar rubros disponibles:
```bash
php artisan tinker --execute="
\$rubros = DB::table('rubros')->select('id', 'nombre')->orderBy('id')->get();
echo '=== RUBROS DISPONIBLES ===' . \"\n\";
foreach (\$rubros as \$r) {
    echo \"ID: \$r->id - \$r->nombre\" . \"\n\";
}
"
```

### 2. Limpiar datos existentes (OPCIONAL):
```bash
php artisan tinker --execute="
DB::beginTransaction();
DB::table('detalle_rendiciones')->delete();
DB::table('rendiciones')->delete();
DB::table('det_ordenes')->delete();
DB::table('ordenes')->delete();
DB::table('prestaciones_planes')->delete();
DB::table('prestaciones')->delete();
DB::commit();
echo 'Datos eliminados exitosamente';
"
```

### 2. Verificar que existe el Plan ID=1:
```bash
php artisan tinker --execute="
\$plan = DB::table('planes')->find(1);
if (\$plan) {
    echo 'Plan ID=1 existe: ' . \$plan->nombre;
} else {
    echo 'ERROR: No existe el Plan ID=1';
}
"
```

## Salida del Comando

El comando muestra:
- Progreso de la migración
- Errores por línea (si los hay)
- Tabla resumen final:
  - Líneas procesadas
  - Prestaciones creadas
  - Errores encontrados
  - Rubros usados

### Ejemplo de salida exitosa:
```
Iniciando migración de prestaciones desde: storage/app/prestaciones.csv

Procesadas: 50 prestaciones...
Procesadas: 100 prestaciones...

✓ Migración completada exitosamente
+-----------------------+----------+
| Métrica               | Cantidad |
+-----------------------+----------+
| Líneas procesadas     | 150      |
| Prestaciones creadas  | 148      |
| Errores               | 2        |
| Rubros usados         | 5        |
+-----------------------+----------+
```

## Validaciones del Script

El script valida:
1. ✅ Que el archivo exista
2. ✅ Que cada línea tenga 4 campos
3. ✅ Que los campos obligatorios no estén vacíos
4. ✅ Que el ID del rubro sea numérico
5. ✅ Que el rubro exista en la base de datos
6. ✅ Que el código tenga exactamente 6 dígitos
7. ✅ Que el código sea único (error si existe)
8. ✅ Que el valor_ips sea numérico

## Manejo de Errores

- **Errores no fatales**: Se registran y continúa con la siguiente línea
- **Errores fatales**: Se hace rollback de toda la transacción
- **Códigos duplicados**: Se reporta el error pero no detiene el proceso

## Verificación Post-Migración

### Ver prestaciones creadas:
```bash
php artisan tinker --execute="
echo 'Total prestaciones: ' . DB::table('prestaciones')->count() . \"\n\";
echo 'Total relaciones con planes: ' . DB::table('prestaciones_planes')->count() . \"\n\";
"
```

### Ver por rubro:
```bash
php artisan tinker --execute="
\$rubros = DB::table('prestaciones')
    ->join('rubros', 'prestaciones.rubro_id', '=', 'rubros.id')
    ->select('rubros.nombre', DB::raw('count(*) as total'))
    ->groupBy('rubros.nombre')
    ->get();
foreach (\$rubros as \$r) {
    echo \"\$r->nombre: \$r->total\n\";
}
"
```

## Archivo de Ejemplo

Se incluye un archivo de ejemplo en:
```
storage/app/prestaciones_ejemplo.csv
```

Puedes usarlo para probar el comando o como plantilla para tu archivo real.

## Notas Importantes

⚠️ **ADVERTENCIAS:**
- El comando NO borra datos existentes, solo agrega nuevos
- Si un código ya existe, se reportará error y se saltará esa línea
- Los IDs de rubro deben existir previamente en la tabla `rubros`
- Todas las prestaciones se asignan al Plan ID=1 (asegúrate que existe)
- El proceso usa transacciones: si hay un error fatal, se revierte todo

💡 **RECOMENDACIONES:**
- Haz un backup de la base de datos antes de migrar
- Lista los rubros disponibles antes de preparar tu CSV
- Prueba primero con un archivo pequeño
- Revisa el reporte de errores después de la migración
- Usa el archivo de ejemplo como referencia
