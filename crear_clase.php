<?php
require_once 'conexion.php';
session_start();

// 1) Verificar sesión
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'profesor') {
    header("Location: login.html");
    exit();
}

// 2) Cargar los grupos creados por este profesor
$stmt = $conexion->prepare("
    SELECT id_grupo, nombre_grupo
      FROM grupos
     WHERE id_usuario = :uid
     ORDER BY nombre_grupo
");
$stmt->execute([':uid' => $_SESSION['id_usuario']]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Cargar todas las materias
$m = $conexion->query("
    SELECT id_materia, nombre
      FROM materias
     ORDER BY nombre
");
$materias = $m->fetchAll(PDO::FETCH_ASSOC);

// 4) Cargar todos los profesores
$pr = $conexion->query("
    SELECT id_usuario, nombre
      FROM usuarios
     WHERE tipo = 'profesor'
     ORDER BY nombre
");
$profesores = $pr->fetchAll(PDO::FETCH_ASSOC);

$mensaje  = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_profesor = $_POST['id_profesor']  ?? '';
    $id_grupo    = $_POST['id_grupo']     ?? '';
    $id_materia  = $_POST['id_materia']   ?? '';
    $ciclo       = trim($_POST['ciclo']   ?? '');
    $hini        = $_POST['hora_inicio']  ?? '';
    $hfin        = $_POST['hora_fin']     ?? '';

    if ($id_profesor && $id_grupo && $id_materia && $ciclo && $hini && $hfin) {
        try {
            $ins = $conexion->prepare("
                INSERT INTO clases
                  (id_profesor, id_grupo, id_materia, ciclo, hora_inicio, hora_fin)
                VALUES
                  (:prof, :grupo, :mat, :ciclo, :hini, :hfin)
            ");
            $ins->execute([
                ':prof'  => $id_profesor,
                ':grupo' => $id_grupo,
                ':mat'   => $id_materia,
                ':ciclo' => $ciclo,
                ':hini'  => $hini,
                ':hfin'  => $hfin
            ]);
            $mensaje  = "✅ Clase creada correctamente.";
            $tipo_msg = 'success';
        } catch (PDOException $e) {
            $mensaje  = "❌ Error al crear la clase: " . $e->getMessage();
            $tipo_msg = 'error';
        }
    } else {
        $mensaje  = "❌ Todos los campos son obligatorios.";
        $tipo_msg = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Nueva Clase</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles.css">
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #eef4fb; }
    header, footer { background: #004085; color: #fff; text-align: center; padding: 1rem; }
    main { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 136px); padding: 1rem; }
    .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; width: 100%; max-width: 400px; box-sizing: border-box; }
    .card form { display: flex; flex-direction: column; gap: 1rem; }
    .card label { font-weight: 600; }
    .card select, .card input { padding: .75rem; border: 1px solid #ccd0d5; border-radius: 6px; width: 100%; font-size: .95rem; }
    .card button { padding: .75rem; background: #004085; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; transition: background .2s; }
    .card button:hover { background: #003166; }
    .mensaje { margin-bottom: 1rem; padding: .75rem 1rem; border-radius: 6px; text-align: center; }
    .mensaje.success { background: #d4edda; color: #155724; }
    .mensaje.error   { background: #f8d7da; color: #721c24; }
    .volver { text-align: center; margin-top: 1rem; }
    .volver a { color: #004085; text-decoration: none; font-weight: 600; }
    .volver a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <header>
    <h1>Crear Nueva Clase</h1>
  </header>
  <main>
    <div class="card">
      <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipo_msg ?>">
          <?= htmlspecialchars($mensaje) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="id_profesor">Profesor</label>
        <select name="id_profesor" id="id_profesor" required>
          <option value="">— Selecciona —</option>
          <?php foreach ($profesores as $p): ?>
            <option value="<?= $p['id_usuario'] ?>"
              <?= (isset($_POST['id_profesor']) && $_POST['id_profesor']==$p['id_usuario']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="id_grupo">Grupo</label>
        <select name="id_grupo" id="id_grupo" required>
          <option value="">— Selecciona —</option>
          <?php foreach ($grupos as $g): ?>
            <option value="<?= $g['id_grupo'] ?>"
              <?= (isset($_POST['id_grupo']) && $_POST['id_grupo']==$g['id_grupo']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['nombre_grupo']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="id_materia">Materia</label>
        <select name="id_materia" id="id_materia" required>
          <option value="">— Selecciona —</option>
          <?php foreach ($materias as $m): ?>
            <option value="<?= $m['id_materia'] ?>"
              <?= (isset($_POST['id_materia']) && $_POST['id_materia']==$m['id_materia']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($m['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="ciclo">Ciclo (ej. 2025A)</label>
        <input type="text" name="ciclo" id="ciclo"
               value="<?= htmlspecialchars($_POST['ciclo'] ?? '') ?>" required>

        <label for="hora_inicio">Hora de inicio</label>
        <input type="time" name="hora_inicio" id="hora_inicio"
               value="<?= htmlspecialchars($_POST['hora_inicio'] ?? '') ?>" required>

        <label for="hora_fin">Hora de fin</label>
        <input type="time" name="hora_fin" id="hora_fin"
               value="<?= htmlspecialchars($_POST['hora_fin'] ?? '') ?>" required>

        <button type="submit">Crear Clase</button>
      </form>

      <div class="volver">
        <a href="panel_profesor.php">&larr; Volver al Panel</a>
      </div>
    </div>
  </main>
  <footer>
    &copy; 2025 Mi Aplicación Web
  </footer>
</body>
</html>
