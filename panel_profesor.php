<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'profesor') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Profesor</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <style>
    body { background: #eef4fb; font-family: Arial, sans-serif; margin: 0; display: flex; flex-direction: column; min-height: 100vh; }
    header { background: #004085; color: white; padding: 20px; text-align: center; }
    header h1 { margin: 0; font-size: 24px; }
    main { flex:1; display:flex; justify-content:center; align-items:center; padding:40px; }
    .card { background:white; padding:30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); max-width:600px; width:90%; text-align:center; }
    .card h2 { color:#004085; margin-bottom:10px; }
    .card p { margin-bottom:20px; color:#333; }
    .panel-buttons { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
    .panel-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px; background:#004085; color:white; text-decoration:none; border-radius:6px; font-weight:bold; transition:background 0.3s; }
    .panel-btn:hover { background:#003166; }
    footer { background:#343a40; color:white; text-align:center; padding:15px; }
  </style>
</head>
<body>
  <header>
    <h1>Panel de Control</h1>
    <p>Profesor: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>
  </header>
  <main>
    <section class="card">
      <h2><i class="fas fa-chalkboard-teacher"></i> Bienvenido</h2>
      <p>Elige una opción para gestionar tu clase:</p>
      <div class="panel-buttons">
        <a href="crear_grupo.php" class="panel-btn"><i class="fas fa-users"></i> Crear Grupo</a>
        <a href="crear_clase.php" class="panel-btn"><i class="fas fa-calendar-plus"></i> Crear Clase</a>
        <a href="agregar_alumno.php" class="panel-btn"><i class="fas fa-user-plus"></i> Agregar Alumno</a>
        <a href="index.php" class="panel-btn"><i class="fas fa-id-card"></i> Tomar Asistencia</a>
        <a href="ver_reportes.php" class="panel-btn"><i class="fas fa-chart-bar"></i> Ver Reportes</a>
        <a href="logout.php" class="panel-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
      </div>
    </section>
  </main>
  <footer>
    &copy; 2025 Mi Aplicación Web
  </footer>
</body>
</html>
