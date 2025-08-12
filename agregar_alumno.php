<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'profesor') {
  header("Location: login.html");
  exit();
}

$mensaje_exito = '';

// Importar desde CSV
if (isset($_POST['accion']) && $_POST['accion'] === 'importar_csv' && isset($_FILES['archivo'])) {
  $archivo = $_FILES['archivo']['tmp_name'];
  $id_grupo = $_POST['id_grupo'];
  $contador = 0;
  if (($handle = fopen($archivo, "r")) !== false) {
    fgetcsv($handle); // Saltar encabezado
    while (($datos = fgetcsv($handle)) !== false) {
      if (count($datos) >= 4) {
        [$nombre, $apellido, $matricula, $codigo_rfid] = $datos;
        $stmt = $conexion->prepare("INSERT INTO alumnos (id_grupo, nombre, apellido, matricula, codigo_rfid) VALUES (:id_grupo, :nombre, :apellido, :matricula, :codigo_rfid)");
        $stmt->execute([
          ':id_grupo' => $id_grupo,
          ':nombre' => $nombre,
          ':apellido' => $apellido,
          ':matricula' => $matricula,
          ':codigo_rfid' => $codigo_rfid
        ]);
        $contador++;
      }
    }
    fclose($handle);
    $mensaje_exito = "✅ Se importaron $contador alumno(s) correctamente.";
  }
}

// Acciones: agregar, editar, eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  $accion = $_POST['accion'];

  if ($accion === 'agregar') {
    $stmt = $conexion->prepare("INSERT INTO alumnos (id_grupo, nombre, apellido, matricula, codigo_rfid) VALUES (:id_grupo, :nombre, :apellido, :matricula, :codigo_rfid)");
    $stmt->execute([
      ':id_grupo' => $_POST['id_grupo'],
      ':nombre' => $_POST['nombre'],
      ':apellido' => $_POST['apellido'],
      ':matricula' => $_POST['matricula'],
      ':codigo_rfid' => $_POST['codigo_rfid']
    ]);
    $mensaje_exito = "✅ Alumno agregado correctamente.";
  } elseif ($accion === 'editar') {
    $stmt = $conexion->prepare("UPDATE alumnos SET nombre = :nombre, apellido = :apellido, matricula = :matricula, codigo_rfid = :codigo_rfid WHERE id_alumno = :id_alumno");
    $stmt->execute([
      ':nombre' => $_POST['nombre'],
      ':apellido' => $_POST['apellido'],
      ':matricula' => $_POST['matricula'],
      ':codigo_rfid' => $_POST['codigo_rfid'],
      ':id_alumno' => $_POST['id_alumno']
    ]);
    $mensaje_exito = "✅ Alumno actualizado correctamente.";
  } elseif ($accion === 'eliminar') {
    $stmt = $conexion->prepare("DELETE FROM alumnos WHERE id_alumno = :id_alumno");
    $stmt->execute([':id_alumno' => $_POST['id_alumno']]);
    $mensaje_exito = "🗑️ Alumno eliminado.";
  }
}

// Obtener grupos y alumnos
$stmt = $conexion->prepare("SELECT * FROM grupos WHERE id_usuario = :id_usuario");
$stmt->execute([':id_usuario' => $_SESSION['id_usuario']]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id_grupo_seleccionado = $_POST['id_grupo'] ?? ($_GET['id_grupo'] ?? ($grupos[0]['id_grupo'] ?? null));
$busqueda = $_POST['busqueda'] ?? ($_GET['busqueda'] ?? '');
$alumnos = [];

if ($id_grupo_seleccionado) {
  $stmt = $conexion->prepare("SELECT * FROM alumnos WHERE id_grupo = :id_grupo AND (nombre LIKE :busqueda OR apellido LIKE :busqueda OR matricula LIKE :busqueda)");
  $stmt->execute([
    ':id_grupo' => $id_grupo_seleccionado,
    ':busqueda' => "%$busqueda%"
  ]);
  $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<?php if (!empty($mensaje_exito)): ?>
  <div style="max-width: 800px; margin: 20px auto; padding: 12px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 6px; font-size: 16px; text-align: center;">
    <?= htmlspecialchars($mensaje_exito) ?>
  </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Alumnos</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
      background-color: #eef4fb;
      font-family: Arial, sans-serif;
    }
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      gap: 30px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      width: 90%;
      max-width: 800px;
    }
    .card h3 {
      margin-bottom: 10px;
      color: #007BFF;
    }
    .card form {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .card input[type="text"],
    .card select,
    .card input[type="file"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }
    .card button {
      padding: 10px;
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    .card button:hover {
      background: #0056b3;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table th, table td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: center;
    }
    table td input {
      width: 100%;
      padding: 5px;
      font-size: 14px;
    }
    .acciones button {
      margin: 0 2px;
    }
    .volver {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }
    .panel-btn {
      padding: 10px 20px;
      background: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }
    .panel-btn:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <header>
    <h1 style="text-align: center; padding: 20px; background: #007BFF; color: white;">Gestión de Alumnos</h1>
  </header>

  <main>
    <section class="card">
      <h3>Agregar Alumno</h3>
      <form action="" method="POST">
        <input type="hidden" name="accion" value="agregar">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" placeholder="Apellido" required>
        <input type="text" name="matricula" placeholder="Matrícula" required>
        <input type="text" name="codigo_rfid" placeholder="Código RFID" required>
        <select name="id_grupo" required>
          <option value="">-- Grupo --</option>
          <?php foreach ($grupos as $g): ?>
            <option value="<?= $g['id_grupo'] ?>" <?= $g['id_grupo'] == $id_grupo_seleccionado ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['nombre_grupo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Agregar Alumno</button>
      </form>
    </section>

    <section class="card">
      <h3>Cargar Alumnos desde Archivo CSV</h3>
      <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="importar_csv">
        <select name="id_grupo" required>
          <option value="">-- Grupo --</option>
          <?php foreach ($grupos as $g): ?>
            <option value="<?= $g['id_grupo'] ?>" <?= $g['id_grupo'] == $id_grupo_seleccionado ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['nombre_grupo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="file" name="archivo" accept=".csv" required>
        <button type="submit">📥 Importar CSV</button>
      </form>
      <small>Formato: <strong>nombre, apellido, matricula, codigo_rfid</strong></small>
    </section>

    <?php if (!empty($alumnos)): ?>
    <section class="card">
      <h3>Resultados de Búsqueda</h3>
      <table>
        <thead>
          <tr><th>Nombre</th><th>Apellido</th><th>Matrícula</th><th>RFID</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($alumnos as $al): ?>
          <tr>
            <form method="POST">
              <td><input type="text" name="nombre" value="<?= htmlspecialchars($al['nombre']) ?>"></td>
              <td><input type="text" name="apellido" value="<?= htmlspecialchars($al['apellido']) ?>"></td>
              <td><input type="text" name="matricula" value="<?= htmlspecialchars($al['matricula']) ?>"></td>
              <td><input type="text" name="codigo_rfid" value="<?= htmlspecialchars($al['codigo_rfid']) ?>"></td>
              <td class="acciones">
                <input type="hidden" name="id_alumno" value="<?= $al['id_alumno'] ?>">
                <input type="hidden" name="id_grupo" value="<?= $id_grupo_seleccionado ?>">
                <button type="submit" name="accion" value="editar">✏️</button>
                <button type="submit" name="accion" value="eliminar" onclick="return confirm('¿Eliminar este alumno?')">🗑️</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <?php endif; ?>
  </main>

  <div class="volver">
    <a href="panel_profesor.php" class="panel-btn">⬅️ Volver al Panel</a>
  </div>

  <footer style="text-align:center; padding: 15px; background:#333; color:white;">
    &copy; 2025 Mi Aplicación Web
  </footer>
</body>
</html>

