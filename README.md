Nombre del Proyecto: FisioGest.

Nuestro proyecto es un sistema centralizado para manejar las operaciones diarias de clinicas de fisioterapia.
Este consta de una serie de funcionalidades que abarcan desde la gestión de las citas, los expendientes de los pacientes,  los horarios de los especiaistas, las asignaciones de los equipos hasta los contactos con los proveedores. Este cuenta con un sistema de notificaciones internas que varían en base al rol.
Es una alternativa eficiente a los archivos manuales dispersos o los libros de excel incompletos.

Este funciona en base a roles para la presentación del contenido del sistema. Entre los roles están:
El administrador: que es el que tiene permisos y acceso para todo, ya sea crear, modificar o borrar pacientes/fisioterapeutas. Desde este rol se puede prohibir el acceso al portal de los otros roles. Desde este rol se gestiona el inventario.

El fisioterapeuta: Este puede ver solo los pacientes que tiene asignados, también puede modificar sus horarios, gestionar citas y asignar equipo.

El paciente: Este solo tiene acceso a su propio expediente, pero de vista, no puede modificar. Puede gestionar citas en base a los horarios.


Integrantes:
Karen Esmeralda Portillo Portillo.
Yolanda Isabel Marroquin Ulloa.
Moisés Abelino Ramírez Rubío.
Erick Josué Rivera Velásquez.


## ▶️ Cómo ejecutar

Sigue estos pasos en orden. Saltarte alguno puede generar errores
de foreign key o datos inconsistentes.

1. **Clona el repositorio e instala dependencias**

   ```bash
   git clone <url-del-repositorio>
   cd FisioGest
   composer install
   npm install
   ```

2. **Configura el archivo de entorno**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Edita `.env` con tus credenciales de base de datos**
   (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

4. **Crea la base de datos en MySQL**

   ```sql
   CREATE DATABASE fisiogest
   ```

5. **Ejecuta migraciones y seeders**

   ```bash
   php artisan migrate --seed
   ```

   > ⚠️ Este comando crea todas las tablas y carga los datos de prueba.
   > No uses `migrate:fresh` en producción.

