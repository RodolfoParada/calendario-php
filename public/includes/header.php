<?php
// =========================================================
// 1. INCLUSIÓN DEL HEADER (debes guardarlo en un archivo header.php)
// =========================================================

// Asignar un título antes de incluir el header
$pageTitle = "Calendario de Visitas";

// Simula el contenido de tu archivo 'header.php' aquí, 
// o usa include('header.php'); si lo tienes separado.

// --- INICIO CÁLCULO RUTA BASE (desde tu header) ---
$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptPath);

if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '/';
} else {
    $basePath = rtrim($basePath, '/') . '/';
}
// --- FIN CÁLCULO RUTA BASE ---

// NOTA: El HTML del <header> y el <body> se abre aquí.
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/app.css" />
  
  <style>
      body { font-family: Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
      .main-content { display: flex; flex-grow: 1; padding-top: 50px; } /* Ajuste para el topbar */
      #sidebar { width: 250px; padding: 20px; border-right: 1px solid #ccc; background-color: #f8f8f8; }
      #calendario-vista { flex-grow: 1; padding: 20px; }
      table { width: 100%; border-collapse: collapse; table-layout: fixed; }
      th, td { border: 1px solid #ccc; padding: 10px; text-align: center; height: 100px; vertical-align: top; }
      .resaltado { font-weight: bold; color: #0056b3; } /* Azul más fuerte */
      .visita { background-color: #d4edda; border-radius: 4px; padding: 5px; margin-bottom: 5px; font-size: 0.8em; text-align: left; overflow: hidden; }
      .error-validacion { background-color: #f8d7da; color: #721c24; padding: 10px; margin-top: 10px; margin-bottom: 20px; border-radius: 5px; display: none; }
      .topbar { background-color: #333; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; width: 100%; top: 0; z-index: 100; }
      .topbar .brand { font-weight: bold; font-size: 1.2em; }
      .topbar .nav a { color: white; text-decoration: none; margin-left: 15px; padding: 5px 10px; }
      .topbar .nav .cta { background-color: #007bff; border-radius: 5px; }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">Sistema de Calendario</div>
    <nav class="nav">
      <a href="<?= htmlspecialchars($basePath) ?>index.php">Inicio</a>
      <a href="<?= htmlspecialchars($basePath) ?>trabajadores_listado.php">Trabajadores</a>
      <a href="<?= htmlspecialchars($basePath) ?>listado_liquidaciones.php">Liquidaciones</a>
      <a class="cta" href="<?= htmlspecialchars($basePath) ?>trabajadores_nuevo.php">Nuevo Trabajador</a>
    </nav>
  </header>

  <?php
// Lógica del Calendario
date_default_timezone_set('America/Santiago'); 

$tecnicos = [
    'A' => ['nombre' => 'Técnico A', 'visitas' => 25],
    'B' => ['nombre' => 'Técnico B', 'visitas' => 18],
    'C' => ['nombre' => 'Técnico C', 'visitas' => 32], // Resaltado
    'D' => ['nombre' => 'Técnico D', 'visitas' => 10]
];

$max_visitas = max(array_column($tecnicos, 'visitas'));

// Definición de la semana a mostrar (lunes de la semana actual)
$fecha_inicio_semana = strtotime('last monday');
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

function get_clase_resaltado($visitas, $max_visitas) {
    return $visitas === $max_visitas ? 'resaltado' : '';
}
?>

<div class="main-content">
    <div id="sidebar">
        <h3>Calendario de Visitas</h3>
        <button>+ Nueva Visita</button>
        <hr>
        <h4>Técnicos</h4>
        <ul>
            <?php foreach ($tecnicos as $id => $tecnico): ?>
                <li class="<?php echo get_clase_resaltado($tecnico['visitas'], $max_visitas); ?>">
                    <?php echo $tecnico['nombre']; ?> (<?php echo $tecnico['visitas']; ?> visitas)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div id="calendario-vista">
        <h2>Semana del <?php echo date('d', $fecha_inicio_semana); ?> al <?php echo date('d M, Y', strtotime('+6 days', $fecha_inicio_semana)); ?></h2>
        
        <div class="error-validacion" id="error-msg">
            ⚠️ **Error de Validación:** ¡Asignación duplicada! La hora seleccionada ya está ocupada.
        </div>

        <table>
            <thead>
                <tr>
                    <?php foreach ($dias_semana as $dia): ?>
                        <th><?php echo $dia; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <td>
                            <?php 
                            $dia_actual = strtotime("+$i days", $fecha_inicio_semana);
                            if (date('N', $dia_actual) == 4) { // Ejemplo para el Jueves
                                echo '<div class="visita">Visita: Cliente García - Téc. A</div>';
                            }
                            if (date('N', $dia_actual) == 6) { // Ejemplo para el Sábado
                                echo '<div class="visita">Visita: Cliente Rojo - Téc. C (Ocupado)</div>';
                            }
                            ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                </tbody>
        </table>
    </div>
</div>

  <script>
    // Tu JavaScript para la interactividad y la validación AJAX irá aquí.
    // Ej: Simular un error de validación para la vista:
    // document.getElementById('error-msg').style.display = 'block'; 
</script>

</body>
</html>