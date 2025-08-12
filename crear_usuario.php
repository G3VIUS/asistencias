<?php
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $codigo_profesor = $_POST['codigo_profesor'] ?? null;
    $tipo = $_POST['tipo'];

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conexion->prepare("
            INSERT INTO usuarios (nombre, correo, password, codigo_profesor, tipo)
            VALUES (:nombre, :correo, :password, :codigo_profesor, :tipo)
        ");

        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':correo', $correo);
        $stmt->bindValue(':password', $hash);
        $stmt->bindValue(':codigo_profesor', $codigo_profesor);
        $stmt->bindValue(':tipo', $tipo);

        $stmt->execute();

        echo "<h2>✅ Usuario creado correctamente como '$tipo'</h2>";
        echo "<p><a href='panel.html'>Volver al panel</a></p>";

    } catch (PDOException $e) {
        echo "<h3>❌ Error al crear usuario:</h3><pre>" . $e->getMessage() . "</pre>";
    }
} else {
    echo "Acceso no permitido.";
}
?>
