# Áncora GOH - Versión PHP/PostgreSQL

> Sistema de Generación y Organización de Horarios Académicos
> Versión web implementada en PHP 5 con PostgreSQL 8.x
> Desarrollado como parte de tesis (2010)

## Descripción

Áncora GOH (Generación y Organización de Horarios) es una aplicación web para la gestión automatizada de horarios académicos en instituciones educativas.

Esta es la **segunda iteración** del proyecto Áncora, migrando desde VB6 (2005-2010) a una arquitectura web usando PHP y PostgreSQL.

## Características Principales

### Gestión de Entidades
- **Planificaciones**: Proyectos contenedores para organizar horarios
- **Especialidades**: Carreras o programas académicos
- **Niveles**: Cursos o años dentro de una especialidad
- **Brigadas**: Grupos de estudiantes
- **Asignaturas**: Materias con desglose de actividades
- **Profesores**: Instructores con disponibilidad
- **Lugares**: Aulas y espacios físicos
- **Recursos**: Equipamiento especial (laboratorios, etc.)

### Sistema de Restricciones
- Restricciones nominales (por entidad)
- Restricciones reales (fecha/hora específica)
- Restricciones condicionales
- Herencia de restricciones en tiempo (HRT)
- Prioridades por zona (ZPriori)
- Restricciones por actividad específica

### Motor de Generación
- Algoritmo **MPI** (Matriz de Posibles Inicios) implementado en PostgreSQL
- Procedimientos almacenados para eficiencia
- Soporte para múltiples escenarios simultáneos

### Seguridad
- Sistema multi-usuario con autenticación
- Control de acceso basado en roles (RBAC)
- Auditoría completa de acciones (traza)
- Control de acceso por IP

## Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                      FRONTEND (PHP 5)                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Views     │  │ Controllers │  │   Models    │         │
│  │  (.tpl)     │  │   (add/set) │  │   (*.php)   │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
├─────────────────────────────────────────────────────────────┤
│                  BACKEND (PostgreSQL 8.x)                   │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Tables    │  │ Functions   │  │  Triggers   │         │
│  │   (62)      │  │  (200+)     │  │             │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

### Estructura de Directorios

```
ancora-php/
├── bin/                    # Utilidades CLI
│   ├── adduser.php         # Agregar usuario
│   ├── deluser.php         # Eliminar usuario
│   ├── showusers.php       # Listar usuarios
│   ├── ancoragoh.conf.example # Plantilla de configuración de BD
│   └── security.conf       # Configuración de seguridad
│
├── control/                # Controladores (MVC)
│   ├── Application.php      # Controlador principal
│   ├── data/
│   │   ├── add/            # Operaciones de creación
│   │   ├── set/            # Operaciones de actualización
│   │   └── del/            # Operaciones de eliminación
│   └── view/
│       ├── combo/          # Datos para combos (AJAX)
│       └── gallery/         # Datos para grillas (AJAX)
│
├── dev/                    # Desarrollo
│   ├── cogephp.php         # Generador de código PHP
│   ├── crea_modelo_pg.php  # Generador de modelo PostgreSQL
│   └── sql/                # Scripts SQL
│       ├── tabla.sql       # Definición de tablas
│       ├── generador/      # Funciones del generador MPI
│       ├── organizacion/    # Entidades de negocio
│       ├── recurso/         # Recursos
│       ├── tiempo/          # Tiempo y restricciones
│       ├── seguridad/       # Seguridad
│       ├── matrix/          # Utilidades de matriz
│       └── disparadores/    # Triggers
│
├── lib/                    # Librerías
│   ├── basic/              # Utilidades básicas
│   ├── ancora/             # Utilidades específicas
│   ├── enterprise/         # Características empresariales
│   └── parsers/            # Parsers XML/JSON
│
├── model/                  # Modelos de datos
│   ├── data/
│   │   ├── *_PHP.php       # Entidades
│   │   ├── *_Model.php     # DAO
│   │   ├── *_Collection.php# Colecciones
│   │   └── services/       # Servicios
│   ├── security/           # Modelos de seguridad
│   └── generator/          # Modelos del generador
│
├── view/                   # Vistas (templates)
│   ├── wins/               # Plantillas de ventana
│   ├── combo/              # Plantillas de combos
│   ├── grid/               # Plantillas de grillas
│   └── custom/             # Plantillas personalizadas
│
└── www/                    # Raíz web
    ├── index.php            # Punto de entrada
    ├── js/                  # JavaScript
    └── images/              # Imágenes
```

## Requisitos

### Servidor
- PHP 5.2+ (orientado a objetos)
- PostgreSQL 8.x+
- Servidor web (Apache, Nginx, etc.)

### Configuración
1. Crear base de datos `ancoragoh`
2. Ejecutar scripts SQL en orden:
   - `dev/sql/tabla.sql` (esquema base)
   - `dev/sql/seguridad/` (seguridad)
   - `dev/sql/generador/` (MPI)
3. Copiar `bin/ancoragoh.conf.example` a `bin/ancoragoh.conf` (ignorado por Git) y completar los datos de conexión; ver `docs/modernization/security-privacy.md`

## Base de Datos

### Esquema Principal

```
planificacion (contenedor principal)
├── periodo (semanas/pares)
├── especialidad (carreras)
├── nivel (años)
├── brigada (grupos de estudiantes)
├── grupo (subdivisiones)
├── asignatura (materias)
│   └── desglose (actividades)
│       └── plantilla_de_actividad
│           └── actividad (asignaciones)
├── profesor
├── lugar (aulas)
├── otro_recurso (equipamiento)
├── clasificacion_de_actividad (tipos: teoría, lab)
├── restriccion_* (restricciones)
├── herencia_de_restricciones (HRT)
├── zpriori (prioridades)
├── higiene_* (límites de carga)
├── costo_de_traslado (distancias)
└── seguridad.* (usuarios, roles, auditoría)
```

### Tablas Principales
- **62 tablas** en total
- **140+ foreign keys** para integridad
- **200+ funciones** para lógica de negocio

## Algoritmo MPI en PostgreSQL

El motor de generación está implementado como procedimientos almacenados:

```sql
-- Función principal
SELECT generar('esp_id', 'nivel_id', 'periodo_id', 1, true, 1, false, false);

-- Verificación de slot válido
SELECT posible_inicio('plantilla_id', dia, turno, 'brigada_id', lugares[], profesores[]);
```

Archivos clave en `dev/sql/generador/`:
- `posible_inicio.sql` - Verifica si un slot es válido
- `and_mpi.sql` - Combina disponibilidad de brigadas
- `generar.sql` - Orquestador de generación
- `agrega_actividad.sql` - Crea asignación

## Modelo de Seguridad

```
USUARIOS ──→ ROLES ──→ PERMISOS
   │           │
   └──→ HOST (IP permitidas)
   │
   └──→ TRAZA (auditoría)
```

### Roles Típicos
- Administrador (control total)
- Coordinador (gestión de datos)
- Consultor (solo lectura)

## Historial

| Período | Tecnología | Estado |
|---------|------------|--------|
| 2005-2010 | VB6 (Desktop) | Legacy - Documentado |
| 2010 | PHP 5 + PostgreSQL | Tesis - Descontinuado |

## Relación con Otras Versiones

- **[VB6](https://github.com/rafageist/ancora-vb6)**: Versión original de escritorio
- **PHP**: Primer intento de migración web (tesis)
- **Versión moderna**: Pendiente de desarrollo

## Licencia

GPL v3 (heredado de la versión VB6)

## Autores

- Desarrollado como parte de tesis de grado (2010)
- Basado en el proyecto Áncora VB6 (2005-2010)

---

*Este proyecto representa el primer intento de migración del sistema Áncora a arquitectura web.*
