# Comparación: VB6 vs PHP

> Análisis comparativo entre las versiones VB6 (2005-2010) y PHP/PostgreSQL (tesis 2010)

---

## Resumen Ejecutivo

| Aspecto | VB6 | PHP/PostgreSQL |
|---------|-----|----------------|
| **Período** | 2005-2010 | 2010 (tesis) |
| **Arquitectura** | Desktop | Web |
| **Almacenamiento** | Archivos .anc (texto) | PostgreSQL |
| **Motor de Generación** | VB6 modules | PostgreSQL stored procedures |
| **Líneas de código** | ~50,000+ | 673 PHP + 282 SQL |
| **Tablas** | N/A (archivos) | 62 tablas |
| **Seguridad** | Ninguna | RBAC + auditoría |
| **Multi-usuario** | No | Sí |

---

## 1. Estructura de Datos

### VB6 (.anc files)
```
[ENCABEZADO]
app_name=Áncora
version=1.2.0

[PERIODOS]
id,descripcion,dias,turnos

[ESPECIALIDADES]
id,descripcion

[BRIGADAS]
id,idesp,nivel,matricula

[ASIGNATURAS]
id,idesp,nivel,desglose

[PROFESORES]
id,nombre,restricciones

[LUGARES]
id,capacidad,restricciones
```

**Características:**
- Formato plano de texto
- Secciones delimitadas por corchetes
- CSV dentro de cada sección
- Sin integridad referencial
- Sin transacciones
- Sin control de concurrencia

### PHP/PostgreSQL
```
planificacion
├── periodo
├── especialidad
├── nivel  
├── brigada
├── asignatura
├── profesor
├── lugar
├── otro_recurso
├── clasificacion_de_actividad
├── plantilla_de_actividad
├── actividad
├── restriccion_*
├── herencia_de_restricciones
├── zpriori
└── seguridad.*
```

**Características:**
- 62 tablas normalizadas
- 140+ foreign keys
- Triggers para integridad
- Stored procedures para lógica
- Transacciones ACID
- Auditoría completa (traza)

---

## 2. Mapeo de Entidades

| VB6 (UDT) | PHP/PostgreSQL | Notas |
|-----------|----------------|-------|
| `TBrigada` | `brigada` + `grupo` + `brigada_de_grupo` | PHP tiene más granularidad |
| `TAsig` | `asignatura` + `desglose` + `plantilla_de_actividad` | PHP separa conceptos |
| `TClasif` | `clasificacion_de_actividad` | Similar |
| `TRecurso` (profesor) | `profesor` | Similar |
| `TRecurso` (lugar) | `lugar` | Similar |
| `TRestriccion` | `restriccion_*` (10+ variantes) | PHP tiene más tipos |
| `TActAsignada` | `actividad` | PHP integra actividades |
| - | `periodicidad` | **Nuevo en PHP** |
| - | `higiene_*` | **Nuevo en PHP** |
| - | `receso` | **Nuevo en PHP** |
| - | `costo_de_traslado` | **Nuevo en PHP** |

---

## 3. Sistema de Restricciones

### VB6 (Simplificado)
```vb
Type TRestriccion
    rest(1 To MAX_DIAS, 1 To MAX_TURNOS) As Boolean
    idperiodo As String
End Type

Function estaRestringidoPorHerencia(per, dia, turno, tipo, id) As Boolean
```

### PHP/PostgreSQL (Avanzado)
```
restriccion_de_periodo          -- Restricciones por período
restriccion_de_especialidad     -- Por especialidad
restriccion_de_brigada         -- Por brigada
restriccion_de_asignatura      -- Por asignatura
restriccion_de_profesor        -- Por profesor
restriccion_de_lugar           -- Por lugar
restriccion_de_otro_recurso    -- Por recurso
restriccion_de_clasif_act      -- Por clasificación
restriccion_condicional        -- Condiciones complejas
restriccion_real_*             -- Fechas/horas específicas
restriccion_por_actividad_*    -- Por actividad específica
```

---

## 4. Algoritmo MPI

### VB6 (modDataGenerator.bas)
```vb
Public Function PosibleInicio(per, dia, turno, asg, act, brg, zona) As TMPI_Casilla
    ' En memoria VB6
    ' Matrices globales
End Function
```

### PHP/PostgreSQL (posible_inicio.sql)
```sql
CREATE OR REPLACE FUNCTION posible_inicio(
    idplantilla VARCHAR, dia INTEGER, turno INTEGER, 
    idbrigada VARCHAR, plugares VARCHAR[], pprofesores VARCHAR[]
) RETURNS BOOLEAN AS $$
    -- En PostgreSQL
    -- Funciones auxiliares
$$;
```

### Comparación de Funciones

| Función | VB6 | PHP/SQL |
|---------|-----|---------|
| `PosibleInicio` | ✅ | ✅ |
| `AND_MPI` | ✅ | ✅ |
| `OR_MPI` | ✅ | ❌ |
| `FiltraProfexAct` | ✅ | ✅ |
| `FiltraLugxAct` | ✅ | ✅ |
| `SelectLugarOptimo` | ✅ | ✅ |
| `AsignaActividad` | ✅ | ✅ |

---

## 5. Features Únicos

### VB6 Only

| Feature | Descripción |
|---------|-------------|
| **Exportación HTML** | Genera páginas web estáticas |
| **Exportación Excel** | Usa libExcel.cls |
| **Asistente de 15 pasos** | Wizard para configuración |
| **Herramienta Duplicar** | Copiar escenarios |
| **Redistribución** | Redistribuir actividades |
| **Análisis gráfico** | Gráficos de utilización |
| **Celdas fijas** | Bloquear asignaciones |

### PHP Only

| Feature | Descripción |
|---------|-------------|
| **Sistema de planificación** | `planificacion` como contenedor |
| **Multi-usuario** | Usuarios, roles, permisos |
| **Auditoría completa** | Tabla `seguridad.traza` |
| **Control de acceso por IP** | `seguridad.host` |
| **Recesos** | `receso` para pausas |
| **Higiene** | `higiene_para_profesor/brigada` |
| **Periodicidad avanzada** | Días específicos |
| **Costos de traslado** | `costo_de_traslado` |
| **Recursos por grupos** | Grupos de recursos |
| **Lugares fijos** | `lugar_fijo` por grupo |

---

## 6. Arquitectura

### VB6 (Desktop)
```
┌─────────────────────────────┐
│       frmKernelMain          │
├─────────────────────────────┤
│      MDI Child Forms        │
├─────────────────────────────┤
│    modDataGlobals.bas       │
│  (Globales en memoria)     │
└─────────────────────────────┘
```

### PHP (Web MVC)
```
┌─────────────────────────────┐
│       www/index.php          │
├─────────────────────────────┤
│    control/Application.php   │
├─────────────────────────────┤
│    Controllers (add/set/del)│
├─────────────────────────────┤
│    Models (*_Model.php)     │
├─────────────────────────────┤
│    PostgreSQL (62 tablas)   │
└─────────────────────────────┘
```

---

## 7. Modelo de Seguridad

### VB6
```
┌────────────────────┐
│     NINGUNO        │
│  Sin autenticación │
│  Sin autorización  │
│  Sin auditoría     │
└────────────────────┘
```

### PHP
```
USUARIOS ──→ ROLES ──→ PERMISOS
   │           │
   └──→ HOST (IP permitidas)
   │
   └──→ TRAZA (auditoría)
```

---

## 8. Lo Mejor de Cada Uno

### De VB6 a Conservar
1. **Exportación HTML/Excel completa**
2. **Asistente de configuración**
3. **Herramientas de análisis gráfico**
4. **Algoritmo MPI bien documentado**
5. **Heurísticas de selección de lugar**

### De PHP a Conservar
1. **Modelo de planificación multi-proyecto**
2. **Sistema de seguridad RBAC completo**
3. **Auditoría de acciones**
4. **Periodicidad avanzada**
5. **Integridad referencial de BD**

---

## 9. Estado de Cada Versión

| Aspecto | VB6 | PHP |
|---------|-----|-----|
| **Estado** | Funcional | Funcional pero descontinuado |
| **Último mantenimiento** | 2010 | 2010 (tesis) |
| **Dependencias** | VB6 IDE, Windows | PHP 5+, PostgreSQL 8+ |
| **Documentación** | **Completa** | Mínima |
| **Código fuente** | ~50,000 líneas | ~40,000 líneas |

---

## 10. Recomendaciones de Migración

### Arquitectura Sugerida (Versión Moderna)

```
┌────────────────────────────────────────────────────────┐
│                    FRONTEND (Moderno)                   │
│           React/Vue + TypeScript + API REST            │
├────────────────────────────────────────────────────────┤
│                    BACKEND (API)                        │
│           Node.js/Python/Java + PostgreSQL             │
├────────────────────────────────────────────────────────┤
│               MOTOR MPI (Servicio)                     │
│   ┌──────────────────────────────────────────────┐    │
│   │  Algoritmo extraído de VB6/PHP como        │    │
│   │  biblioteca standalone                        │    │
│   └──────────────────────────────────────────────┘    │
├────────────────────────────────────────────────────────┤
│                 BASE DE DATOS                          │
│   PostgreSQL con esquema de PHP + features VB6       │
└────────────────────────────────────────────────────────┘
```

### Features a Incorporar
- [ ] Exportación HTML/Excel de VB6
- [ ] Asistente de VB6
- [ ] Herramientas de análisis de VB6
- [ ] Sistema de planificación de PHP
- [ ] Seguridad RBAC de PHP
- [ ] Auditoría de PHP

---

## 11. Conclusiones

1. **VB6 es más completo funcionalmente** - Tiene exportación, asistente, herramientas
2. **PHP es más robusto arquitectónicamente** - BD relacional, seguridad, auditoría
3. **Ambos comparten el mismo núcleo algorítmico** - MPI, HRT, ZPriori
4. **La versión moderna debe fusionar lo mejor de ambos**

---

*Document Status: 🟢 Complete*
*Last Updated: 2026-04-06*
