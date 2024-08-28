<?php

require_once 'database.php';

class Formulario {
    private $conn;
    private $table_name = "citas";

    public $id_cita;
    public $nombre_propietario;
    public $numero_contacto;
    public $nombre_mascota;
    public $raza_mascota;
    public $servicio;
    public $fecha_cita;
    public $hora_cita;
    public $id_mascota;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conn;
    }

    // Método para obtener datos de la mascota y del propietario usando el ID del usuario
    public function getMascotaDataByUserId($user_id) {
        $query = "SELECT u.nombre AS nombre_propietario, u.telefono AS numero_contacto, m.nombre_mascota, m.raza AS raza_mascota 
                  FROM usuario u 
                  LEFT JOIN mascota m ON u.id = m.id 
                  WHERE u.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascota_data = $result->fetch_assoc();
        $stmt->close();
        return $mascota_data;
    }

    // Método para obtener el id_mascota usando el ID del usuario
    public function getMascotaIdByUserId($user_id) {
        $query = "SELECT id_mascota FROM mascota WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $stmt->bind_result($id_mascota); // Esto enlaza el resultado de la consulta con la variable $id_mascota
        $stmt->fetch(); // Obtiene el resultado y lo asigna a la variable enlazada
        $stmt->close();
        
        return $id_mascota; // Ahora $id_mascota está asignada correctamente y se retorna
    }

    public function create() {
        // Verificar si el id_mascota existe en la tabla mascota antes de insertar
        $query = "SELECT id_mascota FROM mascota WHERE id_mascota = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id_mascota);
        $stmt->execute();
        $stmt->store_result();
    
        if ($stmt->num_rows === 0) {
            // Si no se encuentra el id_mascota, retorna un error
            echo "<script>alert('Error: Mascota no encontrada.'); window.location.href='index.php?controller=FormularioController&action=create';</script>";
            return false;
        }
    
        $stmt->close();
    
        // Inserción de datos en la tabla citas
        $query = "INSERT INTO " . $this->table_name . " 
                    (nombre_propietario, numero_contacto, nombre_mascota, raza_mascota, servicio, fecha_cita, hora_cita, id_mascota) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die("Error en la preparación del statement: " . $this->conn->error);
        }
    
        $stmt->bind_param("sssssssi", 
            $this->nombre_propietario, 
            $this->numero_contacto, 
            $this->nombre_mascota, 
            $this->raza_mascota, 
            $this->servicio, 
            $this->fecha_cita, 
            $this->hora_cita,
            $this->id_mascota
        );
    
        $result = $stmt->execute();
        if ($result === false) {
            die("Error en la ejecución del statement: " . $stmt->error);
        }
    
        $stmt->close();
        return $result;
    }

    public function deleteById($id_cita, $mascota_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cita = ? AND id_mascota = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die("Error en la preparación del statement: " . $this->conn->error);
        }
    
        $stmt->bind_param("ii", $id_cita, $mascota_id);
        $result = $stmt->execute();
    
        $stmt->close();
        return $result;
    }

    public function getById($id_cita, $id_mascota) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cita = ? AND id_mascota = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die("Error en la preparación del statement: " . $this->conn->error);
        }
    
        $stmt->bind_param("ii", $id_cita, $id_mascota);
        $stmt->execute();
        $result = $stmt->get_result();
        $cita = $result->fetch_assoc();
    
        $stmt->close();
        return $cita;
    }
    
    

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                    SET nombre_propietario = ?, numero_contacto = ?, nombre_mascota = ?, raza_mascota = ?, servicio = ?, fecha_cita = ?, hora_cita = ?
                    WHERE id_cita = ? AND id_mascota = ?";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die("Error en la preparación del statement: " . $this->conn->error);
        }

        $stmt->bind_param("sssssssii", 
            $this->nombre_propietario, 
            $this->numero_contacto, 
            $this->nombre_mascota, 
            $this->raza_mascota, 
            $this->servicio, 
            $this->fecha_cita, 
            $this->hora_cita,
            $this->id_cita,
            $this->id_mascota
        );

        $result = $stmt->execute();
        if ($result === false) {
            die("Error en la ejecución del statement: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }
    
    public function getAllByMascota($mascota_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_mascota = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die("Error en la preparación del statement: " . $this->conn->error);
        }
    
        $stmt->bind_param("i", $mascota_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $citas = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        return $citas;
    }
}