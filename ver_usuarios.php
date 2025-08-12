<?php
session_start();
require_once 'conexion.php';

// Verificar sesión y rol
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Procesar acciones via POST
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    if ($accion === 'editar_usuario' && isset($_POST['id_usuario'])) {
        // Actualizar usuario
        $stmt = $conexion->prepare(
            "UPDATE usuarios SET nombre = :nombre, correo = :correo, codigo_profesor = :codigo_profesor, tipo = :tipo
             WHERE id_usuario = :id_usuario"
        );
        $stmt->execute([
            ':nombre' => $_POST['nombre'],
            ':correo' => $_POST['correo'],
            ':codigo_profesor' => $_POST['codigo_profesor'],
            ':tipo' => $_POST['tipo'],
            ':id_usuario' => $_POST['id_usuario']
        ]);
        $mensaje = '✅ Usuario actualizado correctamente.';
    } elseif ($accion === 'eliminar_usuario' && isset($_POST['id_usuario'])) {
        // Eliminar usuario
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        $stmt->execute([':id_usuario' => $_POST['id_usuario']]);
        $mensaje = '🗑️ Usuario eliminado.';
    }
}

// Obtener todos los usuarios
$stmt = $conexion->query("SELECT id_usuario, nombre, correo, codigo_profesor, tipo FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ver Usuarios</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body { background: #eef4fb; font-family: Arial, sans-serif; margin: 0; }
    header { background: #343a40; color: white; padding: 20px; text-align: center; }
    main { padding: 30px; max-width: 1000px; margin: auto; }
    .mensaje { background: #d4edda; border:1px solid #c3e6cb; color:#155724; padding:12px; border-radius:5px; text-align:center; margin-bottom:20px; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 12px; border: 1px solid #ddd; }
    th { background: #007BFF; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    input[type="text"], select { width: 100%; padding: 6px; }
    button { padding: 6px 12px; margin: 0 2px; }
    .acciones { display: flex; justify-content: center; }
    .volver { margin-top: 20px; text-align: center; }
    .panel-btn { padding: 10px 20px; background: #007BFF; color: white; text-decoration: none; border-radius: 5px; }
  </style>
</head>
<body>
  <header>
    <h1>Listado de Usuarios</h1>
  </header>
  <main>
    <?php if ($mensaje): ?>
      <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Código Prof.</th>
          <th>Tipo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr>
          <form method="POST">
            <td><?= $u['id_usuario'] ?></td>
            <td><input type="text" name="nombre" value="<?= htmlspecialchars($u['nombre']) ?>"></td>
            <td><input type="text" name="correo" value="<?= htmlspecialchars($u['correo']) ?>"></td>
            <td><input type="text" name="codigo_profesor" value="<?= htmlspecialchars($u['codigo_profesor']) ?>"></td>
            <td>
              <select name="tipo">
                <option value="profesor" <?= $u['tipo']==='profesor'?'selected':'' ?>>Profesor</option>
                <option value="admin" <?= $u['tipo']==='admin'?'selected':'' ?>>Admin</option>
              </select>
            </td>
            <td class="acciones">
              <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
              <button type="submit" name="accion" value="editar_usuario">✏️ Guardar</button>
              <button type="submit" name="accion" value="eliminar_usuario" onclick="return confirm('¿Eliminar este usuario?')">🗑️</button>
            </td>
          </form>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="volver">
      <a href="panel_admin.php" class="panel-btn">⬅️ Volver al Panel</a>
    </div>
  </main>
</body>
</html>
