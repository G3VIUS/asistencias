<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $usuario
            && (
                // Primero intentamos verificar con hash
                (isset($usuario['password']) && password_verify($password, $usuario['password']))
                // Si falla y el campo no es un hash, comparamos en texto plano
                || $password === $usuario['password']
            )
        ) {
            // Autenticación correcta
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['tipo']       = $usuario['tipo'];

            if ($usuario['tipo'] === 'admin') {
                header('Location: panel_admin.php');
                exit();
            } elseif ($usuario['tipo'] === 'profesor') {
                header('Location: panel_profesor.php');
                exit();
            } else {
                echo "<h3>Tipo de usuario desconocido.</h3>";
            }
        } else {
            // Credenciales incorrectas
            echo "<h3>Correo o contraseña incorrectos.</h3>";
            echo "<p><a href='login.html'>Volver al login</a></p>";
        }
    } catch (PDOException $e) {
        echo "<h3>Error en la consulta:</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    }
} else {
    echo "<h3>Acceso denegado.</h3>";
}
?>
