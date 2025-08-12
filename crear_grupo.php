<?php
require_once 'conexion.php';
session_start();

// Verificar sesión de profesor
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'profesor') {
    header("Location: login.html");
    exit();
}

$mensaje  = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_grupo'] ?? '');
    $uid    = $_SESSION['id_usuario'];

    if ($nombre !== '') {
        try {
            $ins = $conexion->prepare("
              INSERT INTO grupos (id_usuario, nombre_grupo)
              VALUES (:uid, :nombre)
            ");
            $ins->execute([
              ':uid'    => $uid,
              ':nombre' => $nombre
            ]);
            $mensaje  = "✅ Grupo «{$nombre}» creado.";
            $tipo_msg = 'success';
        } catch (PDOException $e) {
            $mensaje  = "❌ Error: " . $e->getMessage();
            $tipo_msg = 'error';
        }
    } else {
        $mensaje  = "❌ El nombre del grupo no puede quedar vacío.";
        $tipo_msg = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Grupo</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="styles.css">
  <style>
    /* --- Layout general --- */
    body { margin:0; font-family:Arial,sans-serif; background:#f0f4fa; }
    header { background:#343a40; color:#fff; padding:1rem; text-align:center; }
    main { display:flex; align-items:center; justify-content:center; min-height:calc(100vh - 136px); padding:1rem; }
    footer { background:#343a40; color:#fff; text-align:center; padding:1rem; }

    /* --- Tarjeta y formulario --- */
    .card {
      background:#fff;
      border-radius:10px;
      box-shadow:0 4px 12px rgba(0,0,0,0.1);
      padding:2rem;
      width:100%;
      max-width:360px;
      box-sizing:border-box;
    }
    .card form { display:flex; flex-direction:column; gap:1rem; }
    .card label { font-weight:600; }
    .card input[type="text"] {
      padding:.75rem;
      border:1px solid #ccd0d5;
      border-radius:6px;
      font-size:.95rem;
      width:100%;
    }
    .card button {
      padding:.75rem;
      background:#007bff;
      color:#fff;
      border:none;
      border-radius:6px;
      cursor:pointer;
      font-size:1rem;
      transition:background .2s;
    }
    .card button:hover { background:#0056b3; }

    /* --- Mensajes de estado --- */
    .mensaje {
      margin-bottom:1rem;
      padding:.75rem 1rem;
      border-radius:6px;
      text-align:center;
    }
    .mensaje.success { background:#d4edda; color:#155724; }
    .mensaje.error   { background:#f8d7da; color:#721c24; }

    /* --- Enlace de volver --- */
    .volver { text-align:center; margin-top:1rem; }
    .volver a { color:#007bff; text-decoration:none; }
    .volver a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <header><h1>Crear Nuevo Grupo</h1></header>

  <main>
    <div class="card">
      <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipo_msg ?>">
          <?= htmlspecialchars($mensaje) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <label for="nombre_grupo">Nombre del grupo</label>
        <input
          type="text"
          id="nombre_grupo"
          name="nombre_grupo"
          placeholder="Ej. Segundo A"
          value="<?= htmlspecialchars($_POST['nombre_grupo'] ?? '') ?>"
          required>

        <button type="submit">Crear Grupo</button>
      </form>

      <div class="volver">
        <a href="panel_profesor.php">&larr; Volver al Panel</a>
      </div>
    </div>
  </main>

  <footer>&copy; 2025 Mi Aplicación Web</footer>
</body>
</html>
