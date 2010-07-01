/* ------------------------------ seguridad.rol ------------------------- */ 
-- Roles de usuarios
CREATE TABLE seguridad.rol (
	nombre varchar not null primary key,
	descripcion varchar
);/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.usuario ------------------------- */ 
/*  USUARIOS  */
DROP TABLE IF EXISTS seguridad.usuario CASCADE;
CREATE TABLE seguridad.usuario(
                 id VARCHAR PRIMARY KEY 
                        DEFAULT crear_id_de_replica('ancoragoh', 'seguridad.usuario', 'id', 'seguridad.usuario'),
             nombre VARCHAR NOT NULL,
               info VARCHAR NOT NULL,
             correo VARCHAR NOT NULL,
    momento_ingreso DOUBLE PRECISION NOT NULL default tiempo.moment_now(),
              nick VARCHAR NOT NULL unique,
        contrasena VARCHAR NOT NULL
 );
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.rol_de_usuario ------------------------- */ 
CREATE TABLE seguridad.rol_de_usuario(
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	rol varchar not null references seguridad.rol(nombre) on delete cascade on update cascade,
	primary key(usuario, rol)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.permiso ------------------------- */ 
CREATE TABLE seguridad.permiso(
	permiso varchar not null,
	rol varchar not null references seguridad.rol(nombre) on delete cascade on update cascade,
	primary key (permiso, rol)
);/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.traza ------------------------- */ 
drop table if exists seguridad.traza cascade;
create table seguridad.traza(
	fecha timestamp default now(),
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	accion varchar,
	ip varchar
);
/* --------------------------------------------------------- */ 
/* ------------------------------ seguridad.host ------------------------- */ 
drop table if exists seguridad.host cascade;
create table seguridad.host(
	ip varchar default '*',
	usuario varchar not null references seguridad.usuario(id) on delete cascade on update cascade,
	primary key (ip, usuario)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.planificacion ------------------------- */ 

-- las planificaciones no se archivan,
-- pues las planificaciones que est?n fuera de fecha ya no se ejecutan
-- lo que se puede hacer es desactivarlas o activarlas que seria como publicar o despublicar
-- por cuestiones administrativas

/* PLANIFICACIONES */
 DROP table IF EXISTS planificacion CASCADE;
 CREATE TABLE planificacion(
     id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'planificacion', 'id', 'planificacion'),
     descripcion VARCHAR NOT NULL,
     fecha_inicial BIGINT,
     cant_dias BIGINT,
     activa BOOLEAN default TRUE,
     creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.periodo ------------------------- */ 
DROP table IF EXISTS periodo CASCADE;
CREATE TABLE periodo(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'periodo', 'id', 'periodo'),
    descripcion VARCHAR NOT NULL,
    cant_dias INTEGER default 5,
    cant_turnos INTEGER default 6,
    CHECK (cant_dias >0),
    CHECK (cant_turnos >0),
    hora_inicial DOUBLE PRECISION,
    duracion_del_turno DOUBLE PRECISION,
    planificacion VARCHAR NOT NULL REFERENCES planificacion(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.receso ------------------------- */ 
DROP table IF EXISTS receso CASCADE;
CREATE TABLE receso(
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    despues_del_turno INTEGER,
    duracion DOUBLE PRECISION,
    PRIMARY KEY (periodo, despues_del_turno)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.configuracion.periodicidad ------------------------- */ 
DROP table IF EXISTS periodicidad CASCADE;
CREATE TABLE periodicidad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'periodicidad', 'id', 'periodicidad'),
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    dia INTEGER,
    UNIQUE (periodo,dia),
    CHECK (dia > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.clasificacion_de_actividad ------------------------- */ 
DROP table IF EXISTS clasificacion_de_actividad CASCADE;
CREATE TABLE clasificacion_de_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'clasificacion_de_actividad', 'id', 'clasificacion_de_actividad'),
    descripcion VARCHAR NOT NULL,
    duracion INTEGER default 1,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    prioridad INTEGER NOT NULL default 1,
    gasto INTEGER default 1
);/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.grupo ------------------------- */ 
DROP table IF EXISTS grupo CASCADE;
CREATE TABLE grupo(
    id VARCHAR NOT NULL PRIMARY KEY,
    descripcion VARCHAR NOT NULL
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    planificacion VARCHAR NOT NULL REFERENCES planificacion(id) ON DELETE CASCADE ON UPDATE CASCADE,
    nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE    
);/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.especialidad ------------------------- */ 
DROP table IF EXISTS especialidad CASCADE;
CREATE TABLE especialidad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'especialidad', 'id', 'especialidad'),
    descripcion VARCHAR NOT NULL,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.nivel ------------------------- */ 
DROP table IF EXISTS nivel CASCADE;
CREATE TABLE nivel(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'nivel', 'id', 'nivel'),
    descripcion VARCHAR NOT NULL,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.brigada ------------------------- */ 
/*  BRIGADAS   */
 DROP table IF EXISTS brigada CASCADE;
 CREATE TABLE brigada(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'brigada', 'id', 'brigada'),
    descripcion VARCHAR NOT NULL,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE,
    matricula INTEGER default 0,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );
/* --------------------------------------------------------- */ 
/* ------------------------------ organizacion.brigada_de_grupo ------------------------- */ 
DROP table IF EXISTS brigada_de_grupo CASCADE;
CREATE TABLE brigada_de_grupo(
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (brigada,grupo)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.asignatura ------------------------- */ 

/* ASIGNATURAS */
 DROP table IF EXISTS asignatura CASCADE;
 CREATE TABLE asignatura(
     id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'asignatura', 'id', 'asignatura'),
     descripcion VARCHAR NOT NULL,
     especialidad VARCHAR REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
     nivel VARCHAR NOT NULL REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE,
     creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
 );/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.desglose ------------------------- */ 
DROP table IF EXISTS desglose CASCADE;
CREATE TABLE desglose(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'desglose', 'id', 'desglose'),
    asignatura VARCHAR NOT NULL REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    min_dias INTEGER default 1,
    max_dias INTEGER default 5,
    respetar_orden BOOLEAN default TRUE,
    mismo_dia BOOLEAN default FALSE,
    unique(asignatura,periodo)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.plantilla_de_actividad ------------------------- */ 
DROP table IF EXISTS plantilla_de_actividad CASCADE;
CREATE TABLE plantilla_de_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'plantilla_de_actividad', 'id', 'plantilla_de_actividad'),
    desglose VARCHAR NOT NULL REFERENCES desglose(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    orden INTEGER,
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    unique (desglose,orden),
    exige_profesor BOOLEAN default TRUE,
    exige_lugar BOOLEAN default TRUE,
    actualizacion_interna NOT NULL BOOLEAN default FALSE
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.zpriori.zpriori ------------------------- */ 
DROP table IF EXISTS zpriori CASCADE;
CREATE TABLE zpriori(
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    PRIMARY KEY (clasificacion,periodo,dia,turno),
    CHECK (valor > 0)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.profesor ------------------------- */ 
DROP table IF EXISTS profesor CASCADE;
CREATE TABLE profesor(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'profesor', 'id', 'profesor'),
    nombre VARCHAR,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.lugar ------------------------- */ 
DROP table IF EXISTS lugar CASCADE;
CREATE TABLE lugar(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'lugar', 'id', 'lugar'),
    nombre VARCHAR,
    capacidad INTEGER default 0,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.otro_recurso ------------------------- */ 
DROP table IF EXISTS otro_recurso CASCADE;
CREATE TABLE otro_recurso(
    id VARCHAR NOT NULL PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'otro_recurso', 'id', 'otro_recurso'),
    nombre VARCHAR,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.otro_grupo_de_recursos_para_actividad ------------------------- */ 
DROP table IF EXISTS otro_grupo_de_recursos_para_actividad CASCADE;
CREATE TABLE otro_grupo_de_recursos_para_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'otro_grupo_de_recursos_para_actividad', 'id', 'otro_grupo_de_recursos_para_actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    necesarios INTEGER NOT NULL default 1,
    imprescindibles BOOLEAN NOT NULL default TRUE,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.otro_recurso_para_actividad ------------------------- */ 
DROP table IF EXISTS otro_recurso_para_actividad CASCADE;
CREATE TABLE otro_recurso_para_actividad(
    grupo_de_recursos VARCHAR NOT NULL REFERENCES otro_grupo_de_recursos_para_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (grupo_de_recursos,otro_recurso)
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.actividad ------------------------- */ 
DROP table IF EXISTS actividad CASCADE;
CREATE TABLE actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'actividad', 'id', 'actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    -- el profesor y el lugar no son obligatorios a no ser que la plantilla lo exija
    
    profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR  REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    CHECK ((plantilla_exige_profesor(plantilla) = TRUE AND profesor IS NOT NULL) OR plantilla_exige_profesor(plantilla) = FALSE),
    CHECK ((plantilla_exige_lugar(plantilla) = TRUE AND lugar IS NOT NULL) OR plantilla_exige_lugar(plantilla) = FALSE),
    
    dia INTEGER,
    turno INTEGER,
    momento_de_colocacion DOUBLE PRECISION,
    unique (plantilla,grupo),
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.otros_recursos_usados ------------------------- */ 
DROP table IF EXISTS otros_recursos_usados CASCADE;
CREATE TABLE otros_recursos_usados(
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    actividad VARCHAR NOT NULL REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    creador VARCHAR NOT NULL REFERENCES seguridad.usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (otro_recurso, actividad)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.lugar_para_actividad ------------------------- */ 
DROP table IF EXISTS lugar_para_actividad CASCADE;
CREATE TABLE lugar_para_actividad(
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    actualizacion_interna BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (plantilla,lugar)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.acceso_prohibido ------------------------- */ 
DROP table IF EXISTS acceso_prohibido CASCADE;
CREATE TABLE acceso_prohibido(
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (grupo,lugar)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.lugar_fijo ------------------------- */ 
DROP table IF EXISTS lugar_fijo CASCADE;
CREATE TABLE lugar_fijo(
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (grupo,lugar)
);/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.profesor_para_actividad ------------------------- */ 
DROP table IF EXISTS profesor_para_actividad CASCADE;
CREATE TABLE profesor_para_actividad(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'profesor_para_actividad', 'id', 'profesor_para_actividad'),
    plantilla VARCHAR NOT NULL REFERENCES plantilla_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    profesor VARCHAR NOT NULL REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    prioridad INTEGER NOT NULL default 1,
    unique (plantilla, profesor)
);/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.asignacion.profesor_para_grupo ------------------------- */ 
DROP table IF EXISTS profesor_para_grupo CASCADE;
CREATE TABLE profesor_para_grupo(
    profesor_para_actividad VARCHAR NOT NULL REFERENCES profesor_para_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    grupo VARCHAR NOT NULL REFERENCES grupo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    PRIMARY KEY (profesor_para_actividad,grupo)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ recurso.costo_de_traslado ------------------------- */ 
DROP table IF EXISTS costo_de_traslado CASCADE;
CREATE TABLE costo_de_traslado(
    origen VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    destino VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    costo BIGINT,
    PRIMARY KEY(origen,destino)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.higiene_para_profesor ------------------------- */ 
DROP table IF EXISTS higiene_para_profesor CASCADE;
CREATE TABLE higiene_para_profesor(
    periodo VARCHAR NOT NULL REFERENCES periodo(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    profesor VARCHAR NOT NULL REFERENCES profesor(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    gasto_maximo_diario INTEGER,
    PRIMARY KEY (periodo, profesor, gasto_maximo_diario)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ actividad.higiene.higiene_para_brigada ------------------------- */ 
DROP table IF EXISTS higiene_para_brigada CASCADE;
CREATE TABLE higiene_para_brigada(
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    gasto_maximo_diario INTEGER,
    PRIMARY KEY(periodo, brigada, gasto_maximo_diario)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.herencia_de_restricciones ------------------------- */ 
DROP table IF EXISTS herencia_de_restricciones CASCADE;
CREATE TABLE herencia_de_restricciones(
    id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'herencia_de_restricciones', 'id', 'herencia_de_restricciones'),
    planificacion VARCHAR REFERENCES planificacion(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
    activa BOOLEAN default TRUE,
        
    -- los foreign keys no llevan NOT NULL pues es uno de ellos el que debe llevarlo
    hijo_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    hijo_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    padre_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    padre_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

    CHECK (check_hrt(hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso) = TRUE),
    unique (hijo_brigada, hijo_profesor, hijo_lugar, hijo_otro_recurso, padre_brigada, padre_profesor, padre_lugar, padre_otro_recurso)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.herencia.excepcion_de_hrt ------------------------- */ 
DROP table IF EXISTS excepcion_de_hrt CASCADE;
CREATE TABLE excepcion_de_hrt(
    herencia VARCHAR NOT NULL REFERENCES herencia_de_restricciones(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER NOT NULL,
    turno INTEGER NOT NULL,
    CHECK (dia > 0),
    CHECK (turno > 0),
    PRIMARY KEY (herencia,periodo,dia,turno)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.condicional.restriccion_condicional ------------------------- */ 

-- RESTRICCIONES CONDICIONALES

-- si ocurre tal cosa restringo tal objeto

/* RESTRICCION CONDICIONAL */
 DROP table IF EXISTS restriccion_condicional CASCADE;
 CREATE TABLE restriccion_condicional(
        id VARCHAR PRIMARY KEY DEFAULT crear_id_de_replica('ancoragoh', 'restriccion_condicional', 'id', 'restriccion_condicional'),
        planificacion VARCHAR REFERENCES planificacion(id)  ON DELETE CASCADE ON UPDATE CASCADE, 
        activa BOOLEAN default TRUE,
        
        /** CAUSA O PRECONDICION */   

        /** Los param para especificar actividad no llevan NOT NULL pues NULL significa cualquiera */
        /** Si una actividad se identifica con los atributos no nulos entonces es valida la causa  */
        /** y si todos son nulos tambien es valida la causa                                        */
            
        clasif_act VARCHAR REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        asig VARCHAR REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
        otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        especialidad VARCHAR REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        nivel VARCHAR REFERENCES nivel(id) ON DELETE CASCADE ON UPDATE CASCADE, 

        /* si la actividad o parte de la actividad cae en este rango */
        
        -- PARA RANGO EXACTO
            
        fecha_ini integer default null,
        fecha_fin integer default null,
        hora_ini double precision default null,
        hora_fin double precision default null,
        discreto_exacto boolean default null,
            
        -- PARA RANGO EXACTO RELATIVO A LA PLANIFICACION
        
        dia_ini_planif integer default null,
        dia_fin_planif integer default null,
        hora_ini_planif double precision default null,
        hora_fin_planif double precision default null,
        discreto_exacto_planif boolean default null,
        
        -- PARA RANGO EXACTO RELATIVO AL PERIODO
        
        periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        dia_ini INTEGER default null, 
        dia_fin INTEGER default null, 
        turno_ini INTEGER default null, 
        turno_fin INTEGER default null, 

        discreto BOOLEAN default null,

        /* si la actividad o parte ocurre en correspondiencia */
        /* con los siguientes parametros de tiempo nominal    */
        
        /** @note: los nulos se ignoran */
        
        -- PARA CASO NOMINAL
         -- tiempo de la causa en forma nominal
                 
        hoy BOOLEAN default null, 
        ahora BOOLEAN default null,
         
        dia_relativo_anterior INTEGER default 0,  -- ayer, hace 2 dias
        cant_dias_relativo_anterior INTEGER default 0,  -- cantidad antes de ese dia
        
        CHECK (dia_relativo_anterior > -1),
        CHECK (cant_dias_relativo_anterior > -1),
        
        dia_relativo_posterior INTEGER default 0, -- manana, dentro de 3 dias
        cant_dias_relativo_posterior INTEGER default 0, -- cantidad despues de ese dia

        CHECK (dia_relativo_posterior > -1),
        CHECK (cant_dias_relativo_posterior > -1),
        
        rango_tiempo_relativo_anterior_ini double precision default null, 
        rango_tiempo_relativo_anterior_tamano double precision default null, 

        turno_relativo_anterior integer default null,
        cant_turnos_relativo_anterior integer default null,

        CHECK (turno_relativo_anterior > -1),
        CHECK (cant_turnos_relativo_anterior > -1),

        turno_relativo_posterior INTEGER default 0, -- esto es despues de se que acabe la actividad
                                -- si es 1, entonces es el turno siguiente a la actividad
                                
        cant_turnos_relativo_posterior INTEGER default 0, -- cuantos despues
        
        CHECK (turno_relativo_posterior > -1),
        CHECK (cant_turnos_relativo_posterior > -1),

        rango_tiempo_relativo_posterior_ini double precision default null, 
        rango_tiempo_relativo_posterior_tamano double precision default null, 
                        
        -- entre x dias antes y z dias despues
        
        entre_dias_antes INTEGER default null,
        entre_dias_despues INTEGER default null,
        
        -- entre n turnos antes y m turno despues
        
        entre_turnos_antes integer default null, 
        entre_turnos_despues integer default null, 
        
        entre_tiempo_antes double precision DEFAULT NULL,
        entre_tiempo_despues double precision DEFAULT NULL,
        
        -- cantidad de actividades que coincidan con la condicion
        
        cant_exa INTEGER default 1, /** @significa exactamente */
        cant_min INTEGER default 1, /** @significa al menos o como minimo */
        cant_max INTEGER default 1, /** @significa no mas de, como maximo */

        /** EFECTO o POSTCONDICION */
        
        -- si la causa existe entonces se restringe a ... en el rango ...
        
        consec_brigada VARCHAR REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_profesor VARCHAR REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_lugar VARCHAR REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_otro_recurso VARCHAR REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 

        consec_periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
        consec_dia_ini INTEGER DEFAULT NULL,
        consec_dia_fin INTEGER DEFAULT NULL, 
        consec_turno_ini INTEGER DEFAULT NULL, 
        consec_turno_fin INTEGER DEFAULT NULL, 

        consec_discreto BOOLEAN default TRUE,
        siempre BOOLEAN default null, -- siempre que sea ahora, hoy, hace tres dias, en este periodo....
        valor INTEGER default 1, -- valor de la restriccion

        CHECK (valor > 0)
    );
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_periodo ------------------------- */ 
DROP table IF EXISTS restriccion_de_periodo CASCADE;
CREATE TABLE restriccion_de_periodo(
    id VARCHAR NOT NULL PRIMARY KEY,
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    UNIQUE (periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_especialidad ------------------------- */ 
DROP table IF EXISTS restriccion_de_especialidad CASCADE;
CREATE TABLE restriccion_de_especialidad(
    id VARCHAR NOT NULL PRIMARY KEY,
    especialidad VARCHAR NOT NULL REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (especialidad,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_brigada ------------------------- */ 
DROP table IF EXISTS restriccion_de_brigada CASCADE;
CREATE TABLE restriccion_de_brigada(
    id VARCHAR NOT NULL PRIMARY KEY,
    brigada VARCHAR NOT NULL REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR NOT NULL REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique(brigada,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_asignatura ------------------------- */ 
DROP table IF EXISTS restriccion_de_asignatura CASCADE;
CREATE TABLE restriccion_de_asignatura(
    id VARCHAR NOT NULL PRIMARY KEY,
    asignatura VARCHAR NOT NULL REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (asignatura,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_clasif_act ------------------------- */ 
DROP table IF EXISTS restriccion_de_clasif_act CASCADE;
CREATE TABLE restriccion_de_clasif_act(
    id VARCHAR NOT NULL PRIMARY KEY,
    clasificacion VARCHAR NOT NULL REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    unique (clasificacion,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_profesor ------------------------- */ 
DROP table IF EXISTS restriccion_de_profesor CASCADE;
CREATE TABLE restriccion_de_profesor(
    id VARCHAR NOT NULL PRIMARY KEY,
    profesor VARCHAR NOT NULL REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique (profesor,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_lugar ------------------------- */ 
DROP table IF EXISTS restriccion_de_lugar CASCADE;
CREATE TABLE restriccion_de_lugar(
    id VARCHAR NOT NULL PRIMARY KEY,
    lugar VARCHAR NOT NULL REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    unique (lugar,periodo,dia,turno),
    CHECK (valor > 0)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_de_otro_recurso ------------------------- */ 
DROP table IF EXISTS restriccion_de_otro_recurso CASCADE;
CREATE TABLE restriccion_de_otro_recurso(
    id VARCHAR NOT NULL PRIMARY KEY,
    otro_recurso VARCHAR NOT NULL REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    periodo VARCHAR REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE, 
    dia INTEGER,
    turno INTEGER,
    valor INTEGER default 1,
    por_actividad BOOLEAN default FALSE,
    actividad VARCHAR REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    unique (otro_recurso,periodo,dia,turno),
    CHECK (valor > 0)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real CASCADE;
CREATE TABLE restriccion_real(
    restriccion_nominal VARCHAR NOT NULL,
    periodicidad VARCHAR NOT NULL,
    fecha BIGINT,
    hora_inicial DOUBLE PRECISION,
    hora_final DOUBLE PRECISION,
    por_actividad BOOLEAN NOT NULL,
    actividad VARCHAR, -- puede ser nula, pues todas las restricciones no son por actividad
    objeto VARCHAR NOT NULL,
    CHECK (hora_final > hora_inicial),
    PRIMARY KEY (restriccion_nominal, periodicidad)
);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_periodo ------------------------- */ 
-- Restricciones de periodos
DROP TABLE IF EXISTS restriccion_real_de_periodo CASCADE;
CREATE TABLE restriccion_real_de_periodo(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES periodo(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_asignatura ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_asignatura CASCADE;
CREATE TABLE restriccion_real_de_asignatura(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES asignatura(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_brigada ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_brigada CASCADE;
CREATE TABLE restriccion_real_de_brigada(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_especialidad ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_especialidad CASCADE;
CREATE TABLE restriccion_real_de_especialidad(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES especialidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_profesor ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_profesor CASCADE;
CREATE TABLE restriccion_real_de_profesor(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_lugar ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_lugar CASCADE;
CREATE TABLE restriccion_real_de_lugar(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_otro_recurso ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_otro_recurso CASCADE;
CREATE TABLE restriccion_real_de_otro_recurso( 
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.real.restriccion_real_de_clasif_act ------------------------- */ 
DROP TABLE IF EXISTS restriccion_real_de_clasif_act CASCADE;
CREATE TABLE restriccion_real_de_clasif_act(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_clasif_act(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (periodicidad) REFERENCES periodicidad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (objeto) REFERENCES clasificacion_de_actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal, periodicidad)
) INHERITS (restriccion_real);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad ------------------------- */ 
-- ------------------------------
-- RESTRICCIONES POR ACTIVIDAD
-- ------------------------------

DROP TABLE IF EXISTS restriccion_por_actividad CASCADE;
CREATE TABLE restriccion_por_actividad(
	restriccion_nominal varchar not null,
	actividad varchar not null,
	primary key (restriccion_nominal, actividad)
);
/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_profesor ------------------------- */ 
/** restricciones por actividad de profesores */

CREATE TABLE restriccion_por_actividad_de_profesor(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_profesor(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_lugar ------------------------- */ 
CREATE TABLE restriccion_por_actividad_de_lugar(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_lugar(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_otro_recurso ------------------------- */ 
CREATE TABLE restriccion_por_actividad_de_otro_recurso(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_otro_recurso(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
/* ------------------------------ tiempo.restriccion.nominal.restriccion_por_actividad_de_brigada ------------------------- */ 
CREATE TABLE restriccion_por_actividad_de_brigada(
    FOREIGN KEY (restriccion_nominal) REFERENCES restriccion_de_brigada(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (actividad) REFERENCES actividad(id) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (restriccion_nominal)
) INHERITS (restriccion_por_actividad);/* --------------------------------------------------------- */ 
