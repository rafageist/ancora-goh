# Documentación Técnica - Áncora PHP

> Documentación del sistema Áncora versión PHP/PostgreSQL

---

## Índice de Documentos

### 01. Contexto
| Documento | Descripción | Estado |
|-----------|-------------|--------|
| [Contexto](./01-contexto/) | Modelo de contexto del sistema | 🔄 |

### 02. Arquitectura
| Documento | Descripción | Estado |
|-----------|-------------|--------|
| [Arquitectura General](./02-arquitectura/arquitectura.md) | Visión general del sistema | 🔄 |
| [Base de Datos](./02-arquitectura/base-de-datos.md) | Esquema de 62 tablas | 🔄 |
| [Modelo de Seguridad](./02-arquitectura/seguridad.md) | RBAC y auditoría | 🔄 |

### 03. Algoritmo
| Documento | Descripción | Estado |
|-----------|-------------|--------|
| [MPI en SQL](./03-algoritmo/mpi-sql.md) | Stored procedures del generador | 🔄 |
| [Funciones del Generador](./03-algoritmo/funciones.md) | Lista de funciones SQL | 🔄 |

### 04. Modelos
| Documento | Descripción | Estado |
|-----------|-------------|--------|
| [Modelos PHP](./04-modelos/modelos-php.md) | Clases generadas automáticamente | 🔄 |
| [Patrón Entity-Model-Collection](./04-modelos/patron.md) | Arquitectura de datos | 🔄 |

### 05. Comparación
| Documento | Descripción | Estado |
|-----------|-------------|--------|
| [VB6 vs PHP](./05-comparacion/vb6-php.md) | Análisis comparativo | 🔄 |

### 06. Glosario
| Documento | Descripción | Estado |
|-----------|-------------|--------|
| [Terminología](./06-glosario/) | Glosario de términos | 🔄 |

---

## Herramientas de Desarrollo

### Generadores de Código
El proyecto incluye herramientas para generar código automáticamente:

```
dev/
├── cogephp.php           # Genera clases PHP desde JSON
├── crea_modelo_pg.php    # Genera modelo PostgreSQL
└── src/
    └── *.json           # Metadatos para generación
```

### Compilación SQL
```
dev/sql/
├── compilasql.php        # Compila funciones SQL
├── compilasql.sh         # Script bash
└── compilasql.bat        # Script batch
```

---

## Estados de Documentación

| Símbolo | Significado |
|---------|-------------|
| 🔄 | En progreso |
| 🟢 | Completo |
| ⏳ | Pendiente |
| ❌ | Bloqueado |

---

*Última actualización: 2026-04-06*
