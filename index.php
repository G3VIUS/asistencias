<?php
require_once 'conexion.php';
session_start();

// 1) Verificar sesión de profesor
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'profesor') {
    header("Location: login.html");
    exit();
}

$mensaje     = '';
$tipo_msg    = ''; // 'success' o 'error'
$clases      = [];
$asistencias = [];

// 2) Cargar las clases de este profesor
$stmt = $conexion->prepare("
    SELECT
      c.id_clase,
      g.nombre_grupo,
      c.ciclo,
      c.hora_inicio,
      c.hora_fin
    FROM clases c
    JOIN grupos g ON c.id_grupo = g.id_grupo
    WHERE c.id_profesor = :id_profesor
    ORDER BY c.ciclo DESC, c.hora_inicio DESC
");
$stmt->execute([':id_profesor' => $_SESSION['id_usuario']]);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Manejar envío de asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar') {
    $id_clase = $_POST['id_clase'] ?? '';
    $rfid     = trim($_POST['rfid'] ?? '');

    if ($id_clase && $rfid !== '') {
        // 3.1) Obtengo el grupo al que pertenece la clase
        $g = $conexion->prepare("
            SELECT id_grupo
              FROM clases
             WHERE id_clase = :id_clase
        ");
        $g->execute([':id_clase' => $id_clase]);
        $id_grupo = $g->fetchColumn();

        // 3.2) Validar que ese RFID esté inscrito en este grupo
        $v = $conexion->prepare("
            SELECT id_alumno, nombre, apellido
              FROM alumnos
             WHERE id_grupo    = :id_grupo
               AND codigo_rfid = :rfid
             LIMIT 1
        ");
        $v->execute([
            ':id_grupo' => $id_grupo,
            ':rfid'     => $rfid
        ]);

        if ($al = $v->fetch(PDO::FETCH_ASSOC)) {
            // 3.3) Insertar asistencia incluyendo el grupo, fecha y hora
            $ins = $conexion->prepare("
                INSERT IGNORE INTO asistencias
                    (id_clase, id_grupo, id_alumno, fecha, hora)
                VALUES
                    (:id_clase, :id_grupo, :id_alumno, CURDATE(), CURTIME())
            ");
            $ins->execute([
                ':id_clase'  => $id_clase,
                ':id_grupo'  => $id_grupo,
                ':id_alumno' => $al['id_alumno']
            ]);

            if ($ins->rowCount()) {
                $mensaje  = "✅ Asistencia registrada para {$al['nombre']} {$al['apellido']}.";
                $tipo_msg = 'success';
            } else {
                $mensaje  = "⚠️ Ya se registró asistencia para este alumno en la clase.";
                $tipo_msg = 'error';
            }
        } else {
            $mensaje  = "❌ RFID no pertenece a ningún alumno de este grupo.";
            $tipo_msg = 'error';
        }
    } else {
        $mensaje  = "⚠️ Selecciona una clase e ingresa un código RFID.";
        $tipo_msg = 'error';
    }
}

// 4) Cargar últimas 20 asistencias de la clase seleccionada (o de la primera)
$selected = $_POST['id_clase'] ?? ($clases[0]['id_clase'] ?? null);
if ($selected) {
    $h = $conexion->prepare("
        SELECT
          a.nombre,
          a.apellido,
          asis.fecha,
          asis.hora
        FROM asistencias asis
        JOIN alumnos a ON asis.id_alumno = a.id_alumno
        WHERE asis.id_clase = :id_clase
        ORDER BY asis.fecha DESC, asis.hora DESC
        LIMIT 20
    ");
    $h->execute([':id_clase' => $selected]);
    $asistencias = $h->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Control de Asistencia RFID</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles.css">
  <style>
    body { font-family: Arial, sans-serif; background:#eef4fb; margin:0; }
    .container { max-width:800px; margin:30px auto; padding:0 15px; }
    header { background:#007BFF; color:#fff; padding:20px; text-align:center; }
    .card { background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); margin-top:20px; }
    h1,h2,h3 { color:#007BFF; margin:0 0 15px; }
    form { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
    form select, form input[type="text"] { flex:1; padding:10px; border:1px solid #ccc; border-radius:4px; }
    form button { padding:10px 20px; background:#007BFF; color:#fff; border:none; border-radius:4px; cursor:pointer; }
    form button:hover { background:#0056b3; }
    .msg { margin:15px 0; padding:12px; border-radius:4px; text-align:center; }
    .msg.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    .msg.error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:center; }
    .volver { text-align:center; margin:20px 0; }
    .volver a { color:#007BFF; text-decoration:none; }
    .volver a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <header>
    <h1>Control de Asistencia RFID</h1>
  </header>

  <div class="container">
    <?php if ($mensaje): ?>
      <div class="msg <?= $tipo_msg ?>">
        <?= htmlspecialchars($mensaje) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2>Registrar Asistencia</h2>
      <form action="" method="POST">
        <input type="hidden" name="accion" value="registrar">

        <label for="id_clase">Clase:</label>
        <select name="id_clase" id="id_clase" required onchange="this.form.submit()">
          <?php foreach ($clases as $c): ?>
            <option value="<?= $c['id_clase'] ?>"
              <?= $c['id_clase'] == $selected ? 'selected' : '' ?>>
              <?= htmlspecialchars("{$c['ciclo']} · {$c['nombre_grupo']} ({$c['hora_inicio']}-{$c['hora_fin']})") ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="rfid">Código RFID:</label>
        <input type="text" name="rfid" id="rfid" placeholder="Ingresa código RFID" required>

        <button type="submit">Registrar</button>
      </form>
    </div>

    <div class="card">
      <h3>Últimas Asistencias</h3>
      <table>
        <thead>
          <tr>
            <th>Alumno</th>
            <th>Fecha</th>
            <th>Hora</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($asistencias): ?>
            <?php foreach ($asistencias as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
              <td><?= htmlspecialchars($row['fecha']) ?></td>
              <td><?= htmlspecialchars($row['hora']) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="3">No hay asistencias registradas aún.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="volver">
      <a href="panel_profesor.php">&larr; Volver al Panel</a>
    </div>
  </div>
</body>
</html>
