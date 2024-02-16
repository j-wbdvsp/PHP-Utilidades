<?php
namespace Utilidades\MySQL;

/**
 * Clase SyncTriggers para sincronizar disparadores entre dos bases de datos.
 * @package Utilidades\MySQL
 * @author  Juan Angulo <juan@webdevspain.com>
 * @version 1.0
 */
class SyncTriggers
{
    /**
     * @var bool Control de disparadores en DB Origen.
     */
    private bool $switchTriggers;

    /**
     * @var string Servidor de la base de datos.
     */
    private string $servername;

    /**
     * @var string Nombre de usuario de la base de datos.
     */
    private string $username;

    /**
     * @var string Contraseña de la base de datos.
     */
    private string $password;

    /**
     * @var string Nombre de la base de datos de origen.
     */
    private string $dbnameOrigen;

    /**
     * @var string Nombre de la base de datos de destino.
     */
    private string $dbnameDestino;

    /**
     * @var mysqli Conexión con la base de datos de origen.
     */
    private mysqli $connOrigen;

    /**
     * @var mysqli Conexión con la base de datos de destino.
     */
    private mysqli $connDestino;

    /**
     * Constructor de la clase SyncTriggers.
     *
     * @param string $servername Servidor de la base de datos.
     * @param string $username   Nombre de usuario de la base de datos.
     * @param string $password   Contraseña de la base de datos.
     * @param string $dbnameOrigen Nombre de la base de datos de origen.
     * @param string $dbnameDestino Nombre de la base de datos de destino.
     */
    public function __construct(string $servername, string $username, string $password, string $dbnameOrigen, string $dbnameDestino)
    {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbnameOrigen = $dbnameOrigen;
        $this->dbnameDestino = $dbnameDestino;
        $this->switchTriggers = false;
    }

    /**
     * Activa o desactiva la sincronización de disparadores en la base de datos de origen.
     *
     * @param bool $activar Indica si se debe activar la sincronización de disparadores. Por defecto es true.
     */
    public function installOriginTriggers(bool $activar = true): void
    {
        $this->switchTriggers = $activar;
    }
    
    /**
     * Sincroniza los disparadores entre la base de datos de origen y la base de datos de destino.
     *
     * @throws Exception Si hay un error en la sincronización.
     */
    public function syncTriggers(): void
    {
        try {
            echo "Iniciando la sincronización de disparadores...\n";
            $this->connectOrigen();
            echo "Conexión establecida con la base de datos de origen.\n";
            $this->connectDestino();
            echo "Conexión establecida con la base de datos de destino.\n";
            $this->checkAndDeleteTables();
            echo "Tablas de destino que no existen en origen han sido eliminadas.\n";
            $this->createTablesAndTriggers();
            echo "Disparadores creados en la base de datos de destino.\n";
        } catch (Exception $e) {
            throw new Exception("Error en la sincronización: " . $e->getMessage());
        } finally {
            $this->closeConnections();
        }
    }

    /**
     * Establece la conexión con la base de datos de origen.
     *
     * @throws Exception Si hay un error al conectar.
     */
    private function connectOrigen(): void
    {
        $this->connOrigen = new mysqli($this->servername, $this->username, $this->password, $this->dbnameOrigen);
        if ($this->connOrigen->connect_error) {
            throw new Exception("Error de conexión con la base de datos de origen: " . $this->connOrigen->connect_error);
        }
    }

    /**
     * Establece la conexión con la base de datos de destino.
     *
     * @throws Exception Si hay un error al conectar.
     */
    private function connectDestino(): void
    {
        $this->connDestino = new mysqli($this->servername, $this->username, $this->password, $this->dbnameDestino);
        if ($this->connDestino->connect_error) {
            throw new Exception("Error de conexión con la base de datos de destino: " . $this->connDestino->connect_error);
        }
    }

    /**
     * Cierra las conexiones con las bases de datos.
     */
    private function closeConnections(): void
    {
        $this->connOrigen->close();
        $this->connDestino->close();
    }

    /**
     * Comprueba y elimina las tablas de destino que no existen en origen.
     */
    private function checkAndDeleteTables(): void
    {
        $tablesOrigen = $this->getTables($this->connOrigen);
        $tablesDestino = $this->getTables($this->connDestino);

        foreach ($tablesDestino as $tablaDestino) {
            if (!in_array($tablaDestino, $tablesOrigen)) {
                $this->dropTable($tablaDestino);
            }
        }
    }

    /**
     * Obtiene el listado de tablas de una base de datos.
     *
     * @param mysqli $connection Conexión a la base de datos.
     * @return array Listado de tablas.
     */
    private function getTables(mysqli $connection): array
    {
        $tables = array();
        $sql = "SHOW TABLES";
        $result = $connection->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
        }
        return $tables;
    }

    /**
    * Elimina una tabla de la base de datos de destino.
    *
    * @param string $tableName Nombre de la tabla.
    * @throws Exception Si hay un error al eliminar la tabla.
    */
    private function dropTable(string $tableName): void
    {
        $sql = "DROP TABLE " . $this->dbnameDestino . "." . $tableName;
        if ($this->connDestino->query($sql) === TRUE) {
            echo "Tabla $tableName eliminada exitosamente de la base de datos de destino.\n";
        } else {
            throw new Exception("Error al eliminar la tabla $tableName de la base de datos de destino: " . $this->connDestino->error);
        }
    }

     /**
     * Crea las tablas y los disparadores en la base de datos de destino.
     */
    private function createTablesAndTriggers(): void
    {
        $tables = $this->getTables($this->connOrigen);
        foreach ($tables as $table) {
            if (!$this->tableExistsInDestino($table)) {
                $this->createTableInDestino($table);                
                $this->copyDataToDestino($table);
            }
            if ($this->switchTriggers === true) {
                $this->createTrigger($table);
            }            
        }
    }

    /**
     * Verifica si una tabla existe en la base de datos de destino.
     *
     * @param string $tableName Nombre de la tabla.
     * @return bool True si la tabla existe, False si no.
     */
    private function tableExistsInDestino($tableName): bool
    {
        $sql = "SHOW TABLES LIKE '$tableName'";
        $result = $this->connDestino->query($sql);
        return $result->num_rows > 0;
    }

    /**
    * Crea una tabla en la base de datos de destino.
    *
    * @param string $tableName Nombre de la tabla.
    * @throws Exception Si hay un error al crear la tabla.
    */
    private function createTableInDestino(string $tableName): void
    {
        // Obtener la estructura de la tabla de origen
        $sql = "SHOW CREATE TABLE " . $this->dbnameOrigen . ".`" . $tableName . "`";
        $result = $this->connOrigen->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $createTableStatement = $row['Create Table'];

            // Reemplazar valor predeterminado '0000-00-00 00:00:00' con NULL en campos date/datetime/timestamp tipo ZERO
            if (preg_match("/NOT NULL DEFAULT \'0000-00-00 00:00:00\'|NOT NULL DEFAULT \'0000-00-00\'|NULL DEFAULT \'0000-00-00 00:00:00\'|NULL DEFAULT \'0000-00-00\'/", $createTableStatement)) {
                $createTableStatement = preg_replace("/NOT NULL DEFAULT \'0000-00-00 00:00:00\'/", "NULL DEFAULT NULL", $createTableStatement);
                $createTableStatement = preg_replace("/NOT NULL DEFAULT \'0000-00-00\'/", "NULL DEFAULT NULL", $createTableStatement);
                $createTableStatement = preg_replace("/NULL DEFAULT \'0000-00-00 00:00:00\'/", "NULL DEFAULT NULL", $createTableStatement);
                $createTableStatement = preg_replace("/NULL DEFAULT \'0000-00-00\'/", "NULL DEFAULT NULL", $createTableStatement);
            }

            // Eliminar el prefijo CREATE TABLE de la declaración obtenida
            $createTableStatement = preg_replace("/CREATE TABLE/", "", $createTableStatement);

            // Crear la tabla en la base de datos de destino
            $sql = "CREATE TABLE " . $this->dbnameDestino . "." . "$createTableStatement";

            if (!$this->connDestino->query($sql)) {
                echo $sql . "\n";
                throw new Exception("Error al crear la tabla $tableName de la base de datos de destino: " . $this->connDestino->error);
            } else {
                echo "Tabla $tableName creada en la base de datos de destino.\n";
            }
        } else {
            throw new Exception("No se pudo obtener la estructura de la tabla $tableName de la base de datos de origen.");
        }
    }

    /**
    * Copia los datos de una tabla de origen a la tabla de destino,
    * ajustando los valores de fecha y hora no válidos.
    *
    * @param string $tableName Nombre de la tabla.    
    * @throws Exception Si hay un error al insertar datos en la tabla de destino.
    */
    private function copyDataToDestino($tableName)
    {
        // Obtener los nombres de las columnas de la tabla
        $columnNames = $this->getColumnNames($tableName, $this->connOrigen);

        // Construir la consulta SQL INSERT
        $sqlInsert = "INSERT INTO " . $this->dbnameDestino . ".`$tableName` (";
        $sqlInsert .= implode(", ", array_map(function($columnName) {
            return "`$columnName`"; // Escapar el nombre de la columna
        }, $columnNames)) . ") VALUES (";
        $sqlInsert .= rtrim(str_repeat("?, ", count($columnNames)), ", ") . ")";

        // Preparar la consulta SQL INSERT
        $stmt = $this->connDestino->prepare($sqlInsert);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta SQL INSERT: " . $this->connDestino->error);
        }

        // Obtener los datos de la tabla de origen
        $result = $this->connOrigen->query("SELECT * FROM `$tableName`");
        if (!$result) {
            throw new Exception("Error al obtener los datos de la tabla $tableName de la base de datos de origen: " . $this->connOrigen->error);
        }

        // Recorrer los resultados y ajustar los valores de fecha y hora no válidos
        /*
        $meta=[];
        while ($finfo = $result->fetch_field()) {
            $meta["$finfo->name"]=$finfo;
        } 
        */               
        while ($row = $result->fetch_assoc()) {             
            // Ajustar los valores de fecha y hora no válidos
            foreach ($row as $key => $value) {
                /*
                if(array_key_exists($key, $meta)){
                    if ($meta[$key]->type=="7"){
                        $row[$key] = "1970-01-01";
                    }
                }elseif ($value === '0000-00-00 00:00:00' | $value === '0000-00-00') {
                    $row[$key] = "1971-01-01";
                }
                */
                if ($value === '0000-00-00 00:00:00' | $value === '0000-00-00') {
                    $row[$key] = "1971-01-01";
                }
            }
            // Ejecutar la consulta SQL INSERT con los valores ajustados
            $types = str_repeat("s", count($columnNames)); // Assuming all values are strings
            $stmt->bind_param($types, ...array_values($row));
            if (!$stmt->execute()) {
                throw new Exception("Error al insertar datos en la tabla $tableName de la base de datos de destino: " . $stmt->error);
            }
        }

        // Liberar recursos
        $stmt->close();
        $result->close();
    }

    /**
     * Obtiene los nombres de las columnas de una tabla.
     *
     * @param string $tableName Nombre de la tabla.
     * @param mysqli $connection Conexión a la base de datos.
     * @return array Nombres de las columnas.
     */
    private function getColumnNames($tableName, $connection): array
    {
        $columns = array();
        $result = $connection->query("SELECT * FROM `$tableName` LIMIT 1");
        if ($result) {
            while ($field = mysqli_fetch_field($result)) {
                $columns[] = $field->name;
            }
            mysqli_free_result($result);
        }
        return $columns;
    }

    /**
    * Crea los disparadores para una tabla en la base de datos de destino.
    *
    * @param string $table Nombre de la tabla.
    * @throws Exception Si hay un error al crear los disparadores.
    */
    private function createTrigger(string $table): void
    {
        // Escapar el nombre de la tabla
        $tableEscaped = $this->connOrigen->real_escape_string($table);
        // Construir el nombre del disparador para INSERT
        $triggerNameInsert = $tableEscaped . "_insert";
        // Construir el nombre del disparador para UPDATE
        $triggerNameUpdate = $tableEscaped . "_update";
        // Construir el nombre del disparador para DELETE
        $triggerNameDelete = $tableEscaped . "_delete";

        if ($this->hasPrimaryKey($table)) {
            $sqlInsert = "CREATE TRIGGER `" . $triggerNameInsert . "` AFTER INSERT ON " . $this->dbnameOrigen . ".`" . $tableEscaped . "`
                FOR EACH ROW
                BEGIN
                    INSERT INTO " . $this->dbnameDestino . ".`" . $tableEscaped . "` SELECT * FROM " . $this->dbnameOrigen . ".`" . $tableEscaped . "` WHERE id = NEW.id;
                END";

            $sqlUpdate = "CREATE TRIGGER `" . $triggerNameUpdate . "` AFTER UPDATE ON " . $this->dbnameOrigen . ".`" . $tableEscaped . "`
                FOR EACH ROW
                BEGIN
                    DELETE FROM " . $this->dbnameDestino . ".`" . $tableEscaped . "` WHERE id = NEW.id;
                    INSERT INTO " . $this->dbnameDestino . ".`" . $tableEscaped . "` SELECT * FROM " . $this->dbnameOrigen . ".`" . $tableEscaped . "` WHERE id = NEW.id;
                END";

            $sqlDelete = "CREATE TRIGGER `" . $triggerNameDelete . "` AFTER DELETE ON " . $this->dbnameOrigen . ".`" . $tableEscaped . "`
                FOR EACH ROW
                BEGIN
                    DELETE FROM " . $this->dbnameDestino . ".`" . $tableEscaped . "` WHERE id = OLD.id;
                END";
        } else {
            $sqlInsert = "CREATE TRIGGER `" . $triggerNameInsert . "` AFTER INSERT ON " . $this->dbnameOrigen . ".`" . $tableEscaped . "`
                FOR EACH ROW
                BEGIN
                    DELETE FROM " . $this->dbnameDestino . ".`" . $tableEscaped . "`;
                    INSERT INTO " . $this->dbnameDestino . ".`" . $tableEscaped . "` SELECT * FROM " . $this->dbnameOrigen . ".`" . $tableEscaped . "`;
                END";

            $sqlUpdate = "CREATE TRIGGER `" . $triggerNameUpdate . "` AFTER UPDATE ON " . $this->dbnameOrigen . ".`" . $tableEscaped . "`
                FOR EACH ROW
                BEGIN
                    DELETE FROM " . $this->dbnameDestino . ".`" . $tableEscaped . "`;
                    INSERT INTO " . $this->dbnameDestino . ".`" . $tableEscaped . "` SELECT * FROM " . $this->dbnameOrigen . ".`" . $tableEscaped . "`;
                END";

            $sqlDelete = "CREATE TRIGGER `" . $triggerNameDelete . "` AFTER DELETE ON " . $this->dbnameOrigen . ".`" . $tableEscaped . "`
                FOR EACH ROW
                BEGIN
                    DELETE FROM " . $this->dbnameDestino . ".`" . $tableEscaped . "`;
                END";
        }
        // Ejecutar consultas para crear los disparadores de INSERT, UPDATE y DELETE
        $this->executeQuery($this->connOrigen, $sqlInsert, "Disparador para INSERT de " . $tableEscaped);
        $this->executeQuery($this->connOrigen, $sqlUpdate, "Disparador para UPDATE de " . $tableEscaped);
        $this->executeQuery($this->connOrigen, $sqlDelete, "Disparador para DELETE de " . $tableEscaped);
    }

    /**
    * Verifica si una tabla tiene una clave primaria.
    *
    * @param string $tableName Nombre de la tabla.
    * @return bool True si la tabla tiene una clave primaria, False en caso contrario.
    */
    private function hasPrimaryKey(string $tableName): bool
    {
        $sql = "SHOW COLUMNS FROM $tableName";
        $result = $this->connOrigen->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['Key'] === 'PRI') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Ejecuta una consulta SQL en la base de datos.
    *
    * @param mysqli $connection Conexión a la base de datos.
    * @param string $sql Consulta SQL a ejecutar.
    * @param string $successMessage Mensaje de éxito.
    * @throws Exception Si hay un error al ejecutar la consulta.
    */
    private function executeQuery(mysqli $connection, string $sql, string $successMessage): void
    {
        if ($connection->query($sql) !== TRUE) {
            throw new Exception("Error al crear $successMessage: " . $connection->error);
        }
    }

}

// Uso de la clase SyncTriggers
$syncTriggers = new SyncTriggers("localhost", "username", "password", "dbOrigen", "dbDestino");
$syncTriggers->installOriginTriggers();
$syncTriggers->syncTriggers();
?>