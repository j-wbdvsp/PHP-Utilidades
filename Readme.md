---

# 🛠️ Utilidades

Este paquete contiene una serie de herramientas útiles para el desarrollo web, proporcionadas por WebDevSpain.

## 📝 Descripción

El paquete de Utilidades de WebDevSpain proporciona una serie de herramientas que simplifican tareas comunes en el desarrollo de aplicaciones web PHP. Desde funciones para manipulación de archivos hasta clases para interactuar con bases de datos, este paquete cubre una amplia gama de necesidades que pueden surgir durante el desarrollo.

## ⚙️ Funcionalidades

### 1. Manejo de archivos

- **Lectura y escritura:** Funciones para leer y escribir archivos de texto.
- **Manipulación:** Operaciones para copiar, mover y eliminar archivos y directorios.

### 2. Interacción con bases de datos

- **Conexión:** Clases para establecer conexiones con bases de datos MySQL, PostgreSQL, SQLite, etc.
- **Consultas:** Métodos para ejecutar consultas SQL y obtener resultados de manera sencilla.

### 3. Validación de datos

- **Validación de formularios:** Herramientas para validar datos ingresados por usuarios en formularios web.
- **Sanitización:** Funciones para limpiar y validar datos antes de ser procesados.

## 💻 Uso

Para utilizar las diferentes funcionalidades proporcionadas por este paquete, simplemente importa las clases o funciones necesarias en tu aplicación y comienza a utilizarlas según la documentación proporcionada.

```php
// Ejemplo de sincronizacion de base de datos MySQL
use Utilidades\MySQL\SyncTriggers;

$syncTriggers = new SyncTriggers("localhost", "username", "password", "dbOrigen", "dbDestino");
$syncTriggers->installOriginTriggers();
$syncTriggers->syncTriggers();
```

## 🤝 Contribución

Si deseas contribuir a este paquete, por favor sigue los siguientes pasos:

1. Haz un fork del repositorio
2. Crea una nueva rama (`git checkout -b feature/nueva-caracteristica`)
3. Realiza tus cambios y haz commit de ellos (`git commit -am 'Agrega una nueva característica'`)
4. Haz push de la rama (`git push origin feature/nueva-caracteristica`)
5. Crea un nuevo Pull Request

## ✍️ Autores

- Juan Angulo ([@j-wbdvsp](https://github.com/j-wbdvsp))

## 📄 Licencia

Este paquete está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

---