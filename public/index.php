<?php
// public/index.php

// 1. CORRECCIÓN DE RUTAS: La carpeta 'src' ya no existe. Ahora subimos un nivel (..) y entramos a 'classes' o 'config'.
// Asumiendo la nueva estructura: /calendario/public/index.php -> /calendario/classes/
require_once __DIR__ . '/../classes/Tecnico.php';
require_once __DIR__ . '/../classes/Cliente.php';
require_once __DIR__ . '/../classes/Visita.php';

// 2. CORRECCIÓN DE DEPENDENCIA: Se ELIMINA la dependencia de '../../env.php'.
// La configuración DEBE ser integrada en Db.php.
require_once __DIR__ . '/../config/Db.php';

// Modelos
try {
    $tecnicoModel = new Tecnico();
    $clienteModel = new Cliente();
    $visitaModel  = new Visita();

    // Datos para los select
    $tecnicos = $tecnicoModel->getAll();
    $clientes = $clienteModel->getAll();

} catch (Exception $e) {
    // Manejo de error de conexión si Db.php falla
    $error_msg_db = "Error de conexión o carga de modelos: " . $e->getMessage();
    $tecnicos = [];
    $clientes = [];
}

// Manejo de semana
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$inicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fecha)));

// Intentar cargar visitas si no hay error de DB
$visitasSemana = [];
if (!isset($error_msg_db)) {
    try {
        $visitasSemana = $visitaModel->getVisitasByWeek($inicioSemana);
    } catch (Exception $e) {
        $error_msg_db = "Error al obtener visitas: " . $e->getMessage();
    }
}


// Array por día
$diasSemana = [];
for ($i = 0; $i < 7; $i++) {
    $dia = date('Y-m-d', strtotime("$inicioSemana +$i days"));
    $diasSemana[$dia] = [];
}

foreach ($visitasSemana as $v) {
    $diasSemana[$v['fecha']][] = $v;
}
// =========================================================
// CONFIGURACIÓN PHP
// =========================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Se asume que la zona horaria es correcta para el usuario
date_default_timezone_set('America/Santiago');

// CÁLCULO DE BASE PATH (corregido para ser más robusto y mantener la estructura)
// Para http://localhost:75/calendario/public/index.php, $basePath debe ser /calendario/public/
$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptPath);
$basePath = ($basePath === '/' || $basePath === '\\' || $basePath === '.') ? '' : $basePath;
$basePath = rtrim($basePath, '/') . '/'; // Deja el slash al final, por ejemplo /calendario/public/

$pageTitle = "Calendario de Visitas";
$fecha_hoy_formateada = date('d M Y');


// =========================================================
// LÓGICA DEL CALENDARIO
// =========================================================
$fecha_inicio_semana = strtotime('last monday');
$dias_semana = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

function generar_cabeceras_semana($fecha_timestamp, $dias_semana) {
    $output = '';
    $start = strtotime('last monday', $fecha_timestamp);
    for ($i=0; $i<7; $i++) {
        $ts = strtotime("+$i days", $start);
        $dia = $dias_semana[$i];
        $numero = date('j', $ts);
        $output .= "<th data-day-index='$i'>$dia<br><span class='day-number'>$numero</span></th>";
    }
    return ['days' => $output];
}
$cabeceras_iniciales = generar_cabeceras_semana(time(), $dias_semana);

// Antes de usar $db
// $db = Db::getInstance()->pdo(); // Ya se inicializa en los modelos si no hay error de DB

// Ahora sí puedes hacer la consulta
$tecnicos_visitas = [];
if (!isset($error_msg_db)) {
    try {
        $db = Db::getInstance()->pdo();
        $sql = "
            SELECT t.nombre AS tecnico, COUNT(v.Id_Visita) AS total_visitas
            FROM Tecnico t
            LEFT JOIN Visita v ON t.Id_Tecnico = v.Id_Tecnico
            GROUP BY t.Id_Tecnico
            ORDER BY total_visitas DESC
        ";
        $stmt = $db->query($sql);
        $tecnicos_visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $tecnicos_visitas = []; // Para evitar errores si falla la consulta
        // Si hay error, lo reportamos en la interfaz
        $error_msg_db = "Error al obtener resumen de técnicos: " . $e->getMessage();
    }
}


?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= htmlspecialchars($pageTitle) ?></title>

<link rel="stylesheet" href="<?= $basePath ?>assets/CSS/style.css"> 


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
/* ==========================================================
   LAYOUT FIJO: FORMULARIO IZQUIERDA + CALENDARIO DERECHA
   ========================================================== */
/* Se asume que este es el CSS del layout */
</style>

</head>
<body>

<header class="topbar">
    <div class="brand">Sistema de Calendario</div>
    <div class="date-control">
        <div class="date-display-container">
            <input type="text" id="date-input-hidden">
            <span class="current-date" id="calendar-toggle">
                <?= htmlspecialchars($fecha_hoy_formateada) ?>
            </span>
        </div>
    </div>
</header>


<main>

<!-- ===========================================
     CONTENEDOR FIJO (FORM + CALENDARIO)
     =========================================== -->
<div id="layout-principal">

    <!-- ===========================================
         FORMULARIO FIJO (columna izquierda)
         =========================================== -->
    <div id="columna-formulario">

        <h2>Programar una Visita</h2>
         <form method="POST" action="../api/crear_visitas.php">
        <input type="hidden" id="id_visita_edit" name="id_visita_edit" value="">
        <div class="form-visita-group">
            <label class="form-visita-label">Técnico:</label> 
            <select id="tecnico" class="form-visita-control" name="id_tecnico" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($tecnicos as $t): ?>
                        <option value="<?= $t['Id_Tecnico'] ?>">
                            <?= htmlspecialchars($t['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
        </div>

        <div class="form-visita-group">
            <label class="form-visita-label">Cliente:</label>
              <select id="cliente" class="form-visita-control" name="id_cliente" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['Id_Cliente'] ?>">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
        </div>

        <div class="form-visita-group">
            <label class="form-visita-label" for="fecha">Fecha:</label>
            <input type="date" id="fecha" class="form-visita-control" name="fecha" required>
        </div>

        <div class="form-visita-group">
            <label class="form-visita-label" for="hora">Hora:</label>
            <input type="time" id="hora" class="form-visita-control" name="hora" required>
        </div>

      

              <button type="submit" id="btn-guardar" class="form-visita-button tertiary full-width">Agregar</button>
         </form>
    </div>

    <!-- ===========================================
         CALENDARIO FIJO (columna derecha)
         =========================================== -->


         
    <div id="columna-calendario">
     

    <h2>Vista Semanal</h2>

    <div id="calendar-controls">
        <span class="date-arrow" id="prev-week">&lt;</span>
        <span class="current-week-range" id="week-display-title">
            Cargando Semana...
        </span>
        <span class="date-arrow" id="next-week">&gt;</span>
    </div>

<div id="status-message-container">
    <?php 
    $error_msg = '';
    $display_style = 'none'; // Por defecto oculto
    $is_error = false;
    $is_success = false;

    if (isset($_GET['error'])) {
        $display_style = 'block';
        $is_error = true;
        if ($_GET['error'] === 'duplicate') {
            $error_msg = '⚠️ Error de Validación: ¡Asignación duplicada! Ya existe una visita para el técnico o el cliente en esa fecha y hora.';
        } elseif ($_GET['error'] === 'missing_data') {
            $error_msg = '⚠️ Error de Validación: ¡Faltan datos obligatorios! Por favor complete todos los campos (Técnico, Cliente, Fecha y Hora).';
        } else {
            $error_msg = '⚠️ Error desconocido.';
        }
        $class_type = 'error-validacion';

    } elseif (isset($_GET['success'])) {
        $display_style = 'block';
        $is_success = true;
        if ($_GET['success'] === '1') {
            $error_msg = '✅ Visita guardada correctamente.';
        } elseif ($_GET['success'] === 'delete_ok') {
            $error_msg = '🗑️ Visita eliminada correctamente.';
        } elseif ($_GET['edit'] === '1') {
            $error_msg = '✏️ Visita actualizada correctamente.';
        } else {
            $error_msg = '✅ Operación completada.';
        }
        $class_type = 'success-validacion'; // Usaremos esta clase para darle un color diferente
    }
    
    // Si hay un mensaje para mostrar, lo renderizamos
    if (!empty($error_msg)): 
    ?>
        <div class="<?= $class_type ?>" id="status-msg" style="display: <?= $display_style ?>;">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>
</div>

    <table>
        <thead id="calendar-thead">
            <tr id="day-number-row">
                <?= $cabeceras_iniciales['days'] ?>
            </tr>
        </thead>

        <tbody>
        <tr>
   <?php for ($i=0; $i<7; $i++): ?>
    <td>
      <?php 
$dia_actual = date('Y-m-d', strtotime("+$i days", strtotime($inicioSemana)));

if (!empty($diasSemana[$dia_actual])) {
    foreach ($diasSemana[$dia_actual] as $v) {

        // Formatear hora si es necesario
        $hora = substr($v["hora"], 0, 5); // ejemplo: 14:30

        // Definir color según estado
        $color = match($v['estado']) {
            'pendiente' => '#FFF4B2', // amarillo pastel
            'realizada' => '#B2FFB2', // verde pastel
            'cancelada' => '#FFB2B2', // rojo pastel
            default => '#E0E0E0',
        };

        echo '<div class="visita" style="background-color:' . $color . '; padding:5px; margin-bottom:5px; border-radius:5px;">
        <label>Estado:</label>
          <select class="estado-visita" data-id="' . $v["Id_Visita"] . '">
          <option value="pendiente" ' . ($v['estado']=='pendiente' ? 'selected' : '') . '>Pendiente</option>
          <option value="realizada" ' . ($v['estado']=='realizada' ? 'selected' : '') . '>Realizada</option>
          <option value="cancelada" ' . ($v['estado']=='cancelada' ? 'selected' : '') . '>Cancelada</option>
       </select>
       <BR>
                <strong>' . htmlspecialchars($hora) . '</strong><br>
                Visita: ' . htmlspecialchars($v["cliente_nombre"]) . '<br>
                Téc.: ' . htmlspecialchars($v["tecnico_nombre"]) . '<br>
                

                <!-- Botón editar -->
                <button class="btn-editar-visita"
                    data-id="' . $v["Id_Visita"] . '"
                    data-cliente="' . $v["Id_Cliente"] . '"
                    data-tecnico="' . $v["Id_Tecnico"] . '"
                    data-fecha="' . $v["fecha"] . '"
                    data-hora="' . $v["hora"] . '"
                    data-estado="' . $v["estado"] . '"
                    style="margin-top:5px;">✏️</button>

                <!-- Botón eliminar -->
                <form method="POST" action="../api/eliminar_visita.php" style="display:inline;">
                    <input type="hidden" name="id_visita" value="' . $v["Id_Visita"] . '">
                    <button type="submit" style="margin-left:5px;" onclick="return confirm(\'¿Desea eliminar esta visita?\');">🗑️</button>
                </form>
              </div>';
    }
}
?>

    </td>
<?php endfor; ?>
        </tr>
        </tbody>
    </table>

    

<div id="resumen-tecnicos">
    <h3>Técnicos y cantidad de visitas asignadas</h3>
    <ul>
        <?php if (!empty($tecnicos_visitas)): ?>
            <?php foreach($tecnicos_visitas as $index => $t): ?>
                <li 
                  <?php if($index === 0 && $t['total_visitas'] > 0): ?>
                      style="font-weight:bold; color:green;"
                  <?php endif; ?>
                >
                    <?= htmlspecialchars($t['tecnico']) ?>: <?= $t['total_visitas'] ?> visitas
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No hay técnicos registrados</li>
        <?php endif; ?>
    </ul>
</div>


</div>





</div> <!-- /layout-principal -->

</main>

<footer class="footer">
    Prototipo Núcleo - Calendario
</footer>

<script>
// BASE PATH pasada correctamente a JS
const basePath = "<?= htmlspecialchars($basePath) ?>";
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= $basePath ?>assets/JS/index.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".btn-editar-visita").forEach(btn => {
        

        btn.addEventListener("click", function (e) {
            e.preventDefault();

            document.getElementById("id_visita_edit").value = this.dataset.id;
            document.getElementById("cliente").value = this.dataset.cliente;
            document.getElementById("tecnico").value = this.dataset.tecnico;
            document.getElementById("fecha").value = this.dataset.fecha;
            document.getElementById("hora").value = this.dataset.hora;

            // La referencia a 'estado' se omite porque no está en el formulario principal.

            document.getElementById("btn-guardar").textContent = "Modificar visita";
        });

    });

    document.querySelectorAll('.estado-visita').forEach(select => {
        select.addEventListener('change', function() {
            const idVisita = this.dataset.id;
            const nuevoEstado = this.value;

            // ✅ CORRECCIÓN DE RUTA DE FETCH: Uso de basePath para la llamada AJAX
            fetch(basePath + '../api/actualizar_visita.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                // Solo enviamos lo necesario para actualizar el estado por JSON
                body: JSON.stringify({ id: idVisita, estado: nuevoEstado })
            })
            .then(res => res.json())
            .then(data => {
                if(data.ok){
                    // Actualizar color en la tarjeta
                    let card = this.closest('.visita');
                    let color = '#E0E0E0';
                    if(nuevoEstado === 'pendiente') color = '#FFF4B2';
                    if(nuevoEstado === 'realizada') color = '#B2FFB2';
                    if(nuevoEstado === 'cancelada') color = '#FFB2B2';
                    card.style.backgroundColor = color;
                } else {
                    alert('Error al actualizar estado');
                }
            })
            .catch(error => console.error('Error en la solicitud:', error));
        });
    });
});
</script>

</body>
</html>