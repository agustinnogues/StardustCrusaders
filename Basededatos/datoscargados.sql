USE sistema_juegos;
-- =====================================================
-- USUARIOS
-- =====================================================
INSERT INTO USUARIO
    (Nombre_Usuario, Correo_Electronico, Contrasena, Rol, Rango)
SELECT
    'Admin',
    'admin@stardust.com',
    '1234',
    1,
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM USUARIO
    WHERE Nombre_Usuario = 'Admin'
);

INSERT INTO USUARIO
    (Nombre_Usuario, Correo_Electronico, Contrasena, Rol, Rango)
SELECT
    'Valentino',
    'valentino@stardust.com',
    '1234',
    0,
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM USUARIO
    WHERE Nombre_Usuario = 'Valentino'
);

INSERT INTO USUARIO
    (Nombre_Usuario, Correo_Electronico, Contrasena, Rol, Rango)
SELECT
    'Lola',
    'lola@stardust.com',
    '1234',
    0,
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM USUARIO
    WHERE Nombre_Usuario = 'Lola'
);

INSERT INTO USUARIO
    (Nombre_Usuario, Correo_Electronico, Contrasena, Rol, Rango)
SELECT
    'Tadeo',
    'tadeo@stardust.com',
    '1234',
    0,
    0
WHERE NOT EXISTS (
    SELECT 1
    FROM USUARIO
    WHERE Nombre_Usuario = 'Tadeo'
);

INSERT INTO USUARIO
    (Nombre_Usuario, Correo_Electronico, Contrasena, Rol, Rango)
SELECT
    'Brahian',
    'brahian@stardust.com',
    '1234',
    0,
    0
WHERE NOT EXISTS (
    SELECT 1
    FROM USUARIO
    WHERE Nombre_Usuario = 'Brahian'
);
-- =====================================================
-- EQUIPOS
-- =====================================================
INSERT INTO EQUIPO (Nombre_Equipo)
SELECT 'Stardust'
WHERE NOT EXISTS (
    SELECT 1
    FROM EQUIPO
    WHERE Nombre_Equipo = 'Stardust'
);

INSERT INTO EQUIPO (Nombre_Equipo)
SELECT 'Crusaders'
WHERE NOT EXISTS (
    SELECT 1
    FROM EQUIPO
    WHERE Nombre_Equipo = 'Crusaders'
);

INSERT INTO EQUIPO (Nombre_Equipo)
SELECT 'Joestar'
WHERE NOT EXISTS (
    SELECT 1
    FROM EQUIPO
    WHERE Nombre_Equipo = 'Joestar'
);
-- =====================================================
-- INTEGRANTES DE EQUIPOS
-- =====================================================
INSERT INTO INTEGRA (ID_U, ID_E)
SELECT u.ID_U, e.ID_E
FROM USUARIO u
CROSS JOIN EQUIPO e
WHERE u.Nombre_Usuario = 'Valentino'
AND e.Nombre_Equipo = 'Stardust'
AND NOT EXISTS (
    SELECT 1
    FROM INTEGRA i
    WHERE i.ID_U = u.ID_U
);

INSERT INTO INTEGRA (ID_U, ID_E)
SELECT u.ID_U, e.ID_E
FROM USUARIO u
CROSS JOIN EQUIPO e
WHERE u.Nombre_Usuario = 'Lola'
AND e.Nombre_Equipo = 'Crusaders'
AND NOT EXISTS (
    SELECT 1
    FROM INTEGRA i
    WHERE i.ID_U = u.ID_U
);

INSERT INTO INTEGRA (ID_U, ID_E)
SELECT u.ID_U, e.ID_E
FROM USUARIO u
CROSS JOIN EQUIPO e
WHERE u.Nombre_Usuario = 'Brahian'
AND e.Nombre_Equipo = 'Joestar'
AND NOT EXISTS (
    SELECT 1
    FROM INTEGRA i
    WHERE i.ID_U = u.ID_U
);
-- =====================================================
-- JUEGOS
-- =====================================================
UPDATE JUEGO
SET Carpeta = 'prueba',
    Descripcion = 'Resuelve sumas para ganar puntos.',
    Puntos_Maximos = 10
WHERE Nombre = 'Juego de Sumas';

UPDATE JUEGO
SET Carpeta = 'Ahorcado',
    Descripcion = 'Adivina la palabra antes de completar el ahorcado.',
    Puntos_Maximos = 100
WHERE Nombre = 'Ahorcado';

UPDATE JUEGO
SET Carpeta = 'LabInf',
    Descripcion = 'Elige el camino correcto para escapar del laberinto.',
    Puntos_Maximos = 100
WHERE Nombre = 'Laberinto Infinito';

INSERT INTO JUEGO (Nombre, Carpeta, Descripcion, Puntos_Maximos)
SELECT
    'Juego de Sumas',
    'prueba',
    'Resuelve sumas para ganar puntos.',
    10
WHERE NOT EXISTS (
    SELECT 1
    FROM JUEGO
    WHERE Nombre = 'Juego de Sumas'
);

INSERT INTO JUEGO (Nombre, Carpeta, Descripcion, Puntos_Maximos)
SELECT
    'Ahorcado',
    'Ahorcado',
    'Adivina la palabra antes de completar el ahorcado.',
    100
WHERE NOT EXISTS (
    SELECT 1
    FROM JUEGO
    WHERE Nombre = 'Ahorcado'
);

INSERT INTO JUEGO (Nombre, Carpeta, Descripcion, Puntos_Maximos)
SELECT
    'Laberinto Infinito',
    'LabInf',
    'Elige el camino correcto para escapar del laberinto.',
    100
WHERE NOT EXISTS (
    SELECT 1
    FROM JUEGO
    WHERE Nombre = 'Laberinto Infinito'
);
-- =====================================================
-- PARTIDAS
-- =====================================================
INSERT INTO JUE_PAR (ID_U, ID_J, Fecha, Puntos)
SELECT u.ID_U, j.ID_J, '2026-09-01 18:30:00', 100
FROM USUARIO u
CROSS JOIN JUEGO j
WHERE u.Nombre_Usuario = 'Valentino'
AND j.Nombre = 'Ahorcado'
AND NOT EXISTS (
    SELECT 1 FROM JUE_PAR jp
    WHERE jp.ID_U = u.ID_U AND jp.ID_J = j.ID_J
    AND jp.Fecha = '2026-09-01 18:30:00'
);

INSERT INTO JUE_PAR (ID_U, ID_J, Fecha, Puntos)
SELECT u.ID_U, j.ID_J, '2026-09-02 19:00:00', 10
FROM USUARIO u
CROSS JOIN JUEGO j
WHERE u.Nombre_Usuario = 'Lola'
AND j.Nombre = 'Juego de Sumas'
AND NOT EXISTS (
    SELECT 1 FROM JUE_PAR jp
    WHERE jp.ID_U = u.ID_U AND jp.ID_J = j.ID_J
    AND jp.Fecha = '2026-09-02 19:00:00'
);

INSERT INTO JUE_PAR (ID_U, ID_J, Fecha, Puntos)
SELECT u.ID_U, j.ID_J, '2026-09-03 18:45:00', 100
FROM USUARIO u
CROSS JOIN JUEGO j
WHERE u.Nombre_Usuario = 'Brahian'
AND j.Nombre = 'Laberinto Infinito'
AND NOT EXISTS (
    SELECT 1 FROM JUE_PAR jp
    WHERE jp.ID_U = u.ID_U AND jp.ID_J = j.ID_J
    AND jp.Fecha = '2026-09-03 18:45:00'
);

INSERT INTO JUE_PAR (ID_U, ID_J, Fecha, Puntos)
SELECT u.ID_U, j.ID_J, '2026-09-04 20:15:00', 0
FROM USUARIO u
CROSS JOIN JUEGO j
WHERE u.Nombre_Usuario = 'Tadeo'
AND j.Nombre = 'Juego de Sumas'
AND NOT EXISTS (
    SELECT 1 FROM JUE_PAR jp
    WHERE jp.ID_U = u.ID_U AND jp.ID_J = j.ID_J
    AND jp.Fecha = '2026-09-04 20:15:00'
);
-- =====================================================
-- ACCIONES ADMINISTRATIVAS SOBRE USUARIOS
-- =====================================================
INSERT INTO ADMIN_U
    (ID_Usu, ID_Adm, Fecha, Accion)
SELECT
    u.ID_U,
    a.ID_U,
    '2026-09-05 17:30:00',
    'Creación de usuario de prueba'
FROM USUARIO u
CROSS JOIN USUARIO a
WHERE u.Nombre_Usuario = 'Valentino'
AND a.Nombre_Usuario = 'Admin';
-- =====================================================
-- ACCIONES ADMINISTRATIVAS SOBRE JUEGOS
-- =====================================================
INSERT INTO ADMIN_J
    (ID_Usu, ID_Jue, Fecha, Accion)
SELECT
    a.ID_U,
    j.ID_J,
    '2026-09-05 18:00:00',
    'Carga inicial del juego'
FROM USUARIO a
CROSS JOIN JUEGO j
WHERE a.Nombre_Usuario = 'Admin'
AND j.Nombre = 'Adivina la Bandera';