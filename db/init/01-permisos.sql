-- Se ejecuta UNA sola vez, al crear el volumen de datos por primera vez.
-- Cualquier otro .sql que pongas en esta carpeta también se cargará entonces
-- (en orden alfabético: 02-tienda.sql, 03-biblioteca.sql...).

-- El usuario de clase puede crear y usar sus propias bases de datos
-- (una por ejercicio), sin tener que trabajar como root.
-- Solo para desarrollo local: en producción, un usuario por aplicación
-- y con los privilegios mínimos.
GRANT ALL PRIVILEGES ON *.* TO 'dwes'@'%';
FLUSH PRIVILEGES;
