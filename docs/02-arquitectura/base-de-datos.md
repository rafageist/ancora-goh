# Base de Datos - Áncora PHP

> Esquema completo de PostgreSQL (62 tablas)

---

## 2.1 Esquema General

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        ESQUEMA: PUBLIC                                  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────┐                                                    │
│  │  planificacion  │  Proyecto/contenedor principal                    │
│  └────────┬────────┘                                                    │
│           │                                                             │
│           ├──────────┬──────────┬──────────┬──────────┐                │
│           │          │          │          │          │                │
│           ▼          ▼          ▼          ▼          ▼                │
│    ┌──────────┐ ┌────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│    │ periodo  │ │ nivel  │ │ especialidad│ lugar  │ │ profesor│       │
│    └────┬─────┘ └────────┘ └──────────┘ └──────────┘ └──────────┘       │
│         │                                                           │
│         ├────┬────┬────────┬────────┬────────┐                        │
│         │    │    │        │        │        │                        │
│         ▼    ▼    ▼        ▼        ▼        ▼                        │
│   ┌────────┐││┌────────┐││┌────────────┐││┌─────────────────┐       │
│   │perio-  ││││brigada ││││asignatura ││││ lugar_para_     │       │
│   │dicidad │││└───┬────┘│││   (multi)  ││││ actividad       │       │
│   └────────┘││    │      ││└─────┬──────┘││└─────────────────┘       │
│             │└────┼──────┘│      │       │                          │
│             │     │        │      ▼       │                          │
│             │     │        │ ┌──────────┐ │                          │
│             │     │        │ │ desglose  │ │                          │
│             │     │        │ └─────┬────┘ │                          │
│             │     │        │       │      │                          │
│             │     │        │       ▼      │                          │
│             │     │        │ ┌──────────┐ │                          │
│             │     │        │ │plantilla_│ │                          │
│             │     │        │ │de_act    │ │                          │
│             │     │        │ └────┬─────┘ │                          │
│             │     │        │      │        │                          │
│             │     │        │      ▼        │                          │
│             │     │        │ ┌──────────┐  │                          │
│             │     │        │ │ actividad│  │                          │
│             │     │        │ └──────────┘  │                          │
│             │     │        │               │                          │
│             │     └────────┼───────────────┘                          │
│             │              │                                          │
│             ▼              ▼                                          │
│   ┌────────────────┐ ┌────────────────┐                               │
│   │clasificacion_de│ │ grupo          │                               │
│   │_actividad     │ └────────────────┘                               │
│   └────────────────┘                                                  │
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │                    RESTRICCIONES                                  │  │
│  ├─────────────────────────────────────────────────────────────────┤  │
│  │ restriccion_de_periodo        restriccion_condicional             │  │
│  │ restriccion_de_especialidad   herencia_de_restricciones          │  │
│  │ restriccion_de_brigada         excepcion_de_hrt                   │  │
│  │ restriccion_de_asignatura      zpriori                           │  │
│  │ restriccion_de_profesor                                           │  │
│  │ restriccion_de_lugar                                              │  │
│  │ restriccion_de_otro_recurso                                      │  │
│  │ restriccion_de_clasif_act                                         │  │
│  └─────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │                    SEGURIDAD (esquema)                           │  │
│  ├─────────────────────────────────────────────────────────────────┤  │
│  │ seguridad.usuario     seguridad.rol_de_usuario                   │  │
│  │ seguridad.rol        seguridad.permiso                           │  │
│  │ seguridad.host       seguridad.traza                             │  │
│  └─────────────────────────────────────────────────────────────────┘  │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                        ESQUEMA: GENERADOR                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  generador.cancelar  ──→  Control de cancelación de generación         │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 2.2 Tablas por Categoría

### Gestión (Core Business)

| Tabla | Descripción | PK | Notas |
|-------|-------------|-----|-------|
| `planificacion` | Proyecto contenedor | id | Contiene todo |
| `periodo` | Semana/par académico | id | Días, turnos, horas |
| `periodicidad` | Días específicos | id, periodo | Para restricciones |
| `especialidad` | Carrera/programa | id | |
| `nivel` | Año/curso | id | |
| `brigada` | Grupo de estudiantes | id | Matrícula, creador |
| `grupo` | Subdivisión de brigada | id | Tiene clasificación |

### Asignaturas y Actividades

| Tabla | Descripción |
|-------|-------------|
| `asignatura` | Materia por especialidad/nivel |
| `desglose` | Actividades por asignatura y período |
| `plantilla_de_actividad` | Tipo de actividad (teoría, lab) |
| `actividad` | Instancia de actividad asignada |
| `clasificacion_de_actividad` | Tipos: teoría, práctica, lab |

### Recursos

| Tabla | Descripción |
|-------|-------------|
| `profesor` | Instructor |
| `lugar` | Aula/espacio físico |
| `otro_recurso` | Equipamiento especial |
| `otro_grupo_de_recursos_para_actividad` | Grupos de recursos |

### Relaciones Cruzadas

| Tabla | Descripción |
|-------|-------------|
| `profesor_para_actividad` | Profesor → Actividad |
| `profesor_para_grupo` | Profesor → Grupo |
| `lugar_para_actividad` | Lugar → Actividad |
| `lugar_fijo` | Aula fija por grupo |
| `brigada_de_grupo` | Brigadas → Grupos |
| `otro_recurso_para_actividad` | Recurso → Actividad |
| `otros_recursos_usados` | Recursos asignados a actividad |

### Restricciones Nominales

| Tabla | Entidad |
|-------|---------|
| `restriccion_de_periodo` | Período |
| `restriccion_de_especialidad` | Especialidad |
| `restriccion_de_brigada` | Brigada |
| `restriccion_de_asignatura` | Asignatura |
| `restriccion_de_profesor` | Profesor |
| `restriccion_de_lugar` | Lugar |
| `restriccion_de_otro_recurso` | Recurso |
| `restriccion_de_clasif_act` | Clasificación |

### Restricciones Avanzadas

| Tabla | Descripción |
|-------|-------------|
| `restriccion_condicional` | Condiciones complejas |
| `herencia_de_restricciones` | HRT (Herencia) |
| `excepcion_de_hrt` | Excepciones a HRT |
| `zpriori` | Prioridades por zona |

### Restricciones Reales (Fecha/Hora específica)

| Tabla |
|-------|
| `restriccion_real` |
| `restriccion_real_de_periodo` |
| `restriccion_real_de_especialidad` |
| `restriccion_real_de_brigada` |
| `restriccion_real_de_asignatura` |
| `restriccion_real_de_profesor` |
| `restriccion_real_de_lugar` |
| `restriccion_real_de_otro_recurso` |
| `restriccion_real_de_clasif_act` |

### Restricciones por Actividad

| Tabla |
|-------|
| `restriccion_por_actividad` |
| `restriccion_por_actividad_de_brigada` |
| `restriccion_por_actividad_de_profesor` |
| `restriccion_por_actividad_de_lugar` |
| `restriccion_por_actividad_de_otro_recurso` |

### Utilidades

| Tabla | Descripción |
|-------|-------------|
| `acceso_prohibido` | Lugares no permitidos para grupos |
| `costo_de_traslado` | Distancias entre lugares |
| `receso` | Bloques de descanso |
| `higiene_para_profesor` | Carga máxima diaria |
| `higiene_para_brigada` | Carga máxima diaria |

### Seguridad (Esquema `seguridad`)

| Tabla | Descripción |
|-------|-------------|
| `seguridad.usuario` | Usuarios (MD5 passwords) |
| `seguridad.rol` | Roles |
| `seguridad.rol_de_usuario` | Usuario → Rol |
| `seguridad.permiso` | Rol → Permiso |
| `seguridad.host` | IP permitidas por usuario |
| `seguridad.traza` | Auditoría de acciones |

---

## 2.3 Detalle de Tablas Principales

### planificacion

```sql
CREATE TABLE planificacion (
    id          VARCHAR PRIMARY KEY,
    descripcion VARCHAR NOT NULL,
    fecha_inicial BIGINT,
    cant_dias   BIGINT,
    activa      BOOLEAN,
    creador     VARCHAR NOT NULL
);
```

### periodo

```sql
CREATE TABLE periodo (
    id                  VARCHAR PRIMARY KEY,
    descripcion         VARCHAR NOT NULL,
    cant_dias           INTEGER,
    cant_turnos         INTEGER,
    hora_inicial        DOUBLE PRECISION,
    duracion_del_turno  DOUBLE PRECISION,
    planificacion       VARCHAR NOT NULL
);
```

### brigada

```sql
CREATE TABLE brigada (
    id          VARCHAR PRIMARY KEY,
    descripcion VARCHAR NOT NULL,
    especialidad VARCHAR NOT NULL,
    nivel       VARCHAR NOT NULL,
    matricula   INTEGER,
    creador     VARCHAR NOT NULL
);
```

### actividad (Asignación)

```sql
CREATE TABLE actividad (
    id          VARCHAR PRIMARY KEY,
    plantilla   VARCHAR NOT NULL,
    grupo       VARCHAR NOT NULL,
    profesor    VARCHAR,
    lugar       VARCHAR,
    dia         INTEGER,
    turno       INTEGER,
    momento_de_colocacion DOUBLE PRECISION,
    creador     VARCHAR NOT NULL
);
```

### herencia_de_restricciones

```sql
CREATE TABLE herencia_de_restricciones (
    id                  VARCHAR PRIMARY KEY,
    planificacion       VARCHAR,
    activa              BOOLEAN,
    hijo_brigada        VARCHAR,
    hijo_profesor       VARCHAR,
    hijo_lugar          VARCHAR,
    hijo_otro_recurso   VARCHAR,
    padre_brigada       VARCHAR,
    padre_profesor      VARCHAR,
    padre_lugar         VARCHAR,
    padre_otro_recurso  VARCHAR
);
```

---

## 2.4 Índices y Constraints

### Foreign Keys (muestra)
```sql
ALTER TABLE brigada ADD FOREIGN KEY (especialidad) REFERENCES especialidad(id);
ALTER TABLE brigada ADD FOREIGN KEY (nivel) REFERENCES nivel(id);
ALTER TABLE actividad ADD FOREIGN KEY (plantilla) REFERENCES plantilla_de_actividad(id);
ALTER TABLE actividad ADD FOREIGN KEY (grupo) REFERENCES grupo(id);
ALTER TABLE profesor_para_actividad ADD FOREIGN KEY (profesor) REFERENCES profesor(id);
ALTER TABLE lugar_para_actividad ADD FOREIGN KEY (lugar) REFERENCES lugar(id);
-- ... 140+ foreign keys total
```

### ON DELETE CASCADE
Todas las FK usan `ON DELETE CASCADE` para mantener integridad referencial.

---

## 2.5 Funciones SQL Principales

### Generador MPI

| Función | Descripción |
|---------|-------------|
| `generar()` | Orquestador principal |
| `posible_inicio()` | Verifica si slot es válido |
| `and_mpi()` | Combina MPI de brigadas |
| `agrega_actividad()` | Crea asignación |
| `dame_duracion_plantilla()` | Obtiene duración |
| `busca_lugar_menos_costoso()` | Selecciona lugar óptimo |

### Restricciones

| Función | Descripción |
|---------|-------------|
| `valor_restriccion_periodo()` | Check período |
| `valor_restriccion_clasif_act()` | Check clasificación |
| `valor_restriccion_brigada()` | Check brigada |
| `valor_restriccion_asignatura()` | Check asignatura |
| `valor_restriccion_profesor()` | Check profesor |
| `valor_restriccion_lugar()` | Check lugar |

### Utilidades

| Función | Descripción |
|---------|-------------|
| `dame_periodo_plantilla()` | Obtiene período |
| `dame_clasif_plantilla()` | Obtiene clasificación |
| `dame_lista_id_profes_asignados()` | Profesores de actividad |
| `dame_lista_id_lugares_asignados()` | Lugares de actividad |

---

## 2.6 Vistas Importantes

```sql
-- Vista de horarios por brigada
CREATE VIEW v_horario_brigada AS
SELECT 
    b.id AS brigada,
    a.dia,
    a.turno,
    pa.clasificacion,
    p.nombre AS profesor,
    l.nombre AS lugar
FROM actividad a
JOIN grupo g ON a.grupo = g.id
JOIN brigada b ON EXISTS (SELECT 1 FROM brigada_de_grupo bg WHERE bg.brigada = b.id AND bg.grupo = g.id)
JOIN plantilla_de_actividad pa ON a.plantilla = pa.id
LEFT JOIN profesor p ON a.profesor = p.id
LEFT JOIN lugar l ON a.lugar = l.id;
```

---

*Document Status: 🔄 In Progress*
*Next: Add table relationship diagram*
