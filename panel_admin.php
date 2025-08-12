<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Administrador</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <style>
    body { background: #f4f6f8; font-family: Arial, sans-serif; margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
    header { background: #343a40; color: white; padding: 20px; text-align: center; }
    main { flex: 1; display: flex; justify-content: center; align-items: center; padding: 30px; }
    .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 90%; max-width: 600px; text-align: center; }
    .card h2 { margin-bottom: 20px; color: #007BFF; }
    .panel-buttons { display: flex; flex-direction: column; gap: 15px; }
    .panel-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 20px; background: #007BFF; color: white; border-radius: 6px; text-decoration: none; font-weight: bold; transition: background 0.3s; }
    .panel-btn:hover { background: #0056b3; }
    footer { background: #343a40; color: white; text-align: center; padding: 15px; }
  </style>
</head>
<body>
  <header>
    <h1>Panel de Administrador</h1>
    <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>
  </header>

  <main>
    <section class="card">
      <h2>¿Qué deseas hacer?</h2>
      <div class="panel-buttons">
        <a href="crear_usuario.html" class="panel-btn"><i class="fas fa-user-plus"></i> Crear Usuario</a>
        <a href="ver_usuarios.php" class="panel-btn"><i class="fas fa-users"></i> Ver Usuarios</a>
        <a href="logout.php" class="panel-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
      </div>
    </section>
  </main>

  <footer>
    &copy; 2025 Mi Aplicación Web
  </footer>
</body>
</html>
