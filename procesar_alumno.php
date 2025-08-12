<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'profesor') {
    header("Location: login.html");
    exit();
}

$nombre = $_POST['nombre'] ?? '';
$matricula = $_POST['matricula'] ?? '';
$codigo_rfid = $_POST['codigo_rfid'] ?? '';
$id_grupo = $_POST['id_grupo'] ?? '';

if ($nombre && $matricula && $codigo_rfid && $id_grupo) {
    try {
        // Verificar si ya existe la matrícula o el RFID
        $verificar = $conexion->prepare("SELECT * FROM alumnos WHERE matricula = :matricula OR codigo_rfid = :codigo_rfid");
        $verificar->execute([
            ':matricula' => $matricula,
            ':codigo_rfid' => $codigo_rfid
        ]);

        if ($verificar->rowCount() > 0) {
            echo "<h3>⚠️ Ya existe un alumno con esa matrícula o código RFID.</h3>";
            echo "<p><a href='agregar_alumno.php'>Volver</a></p>";
            exit();
        }

        // Insertar el nuevo alumno
        $stmt = $conexion->prepare("INSERT INTO alumnos (id_grupo, nombre, matricula, codigo_rfid)
                                    VALUES (:id_grupo, :nombre, :matricula, :codigo_rfid)");
        $stmt->execute([
            ':id_grupo' => $id_grupo,
            ':nombre' => $nombre,
            ':matricula' => $matricula,
            ':codigo_rfid' => $codigo_rfid
        ]);

        echo "<h2>✅ Alumno agregado correctamente.</h2>";
        echo "<p><a href='agregar_alumno.php'>Agregar otro alumno</a></p>";

    } catch (PDOException $e) {
        echo "<h3>Error al agregar alumno:</h3><pre>" . $e->getMessage() . "</pre>";
    }
} else {
    echo "<h3>Todos los campos son obligatorios.</h3>";
}
?>
