<?php
// Cambia esta ruta si tu log está en otro lugar
$log_path = '/Applications/XAMPP/xamppfiles/logs/php_error_log';

if (!file_exists($log_path)) {
    echo "<h2>No se encontró el archivo de log en: $log_path</h2>";
    exit;
}

$lines = file($log_path);
$lines = array_slice($lines, -200); // Solo las últimas 200 líneas para no saturar

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Log de Errores PHP</title>
    <style>
        body { background: #181c20; color: #e0e0e0; font-family: monospace; padding: 2em; }
        pre { background: #23272b; padding: 1em; border-radius: 8px; overflow-x: auto; }
        .error { color: #ff6b6b; }
        .warn { color: #ffe066; }
        .info { color: #4dd0e1; }
        .pedidolog { color: #80cbc4; }
    </style>
</head>
<body>
    <h1>Últimos errores y logs de PHP</h1>
    <pre>";

foreach ($lines as $line) {
    if (strpos($line, '[PEDIDO]') !== false) {
        echo "<span class='pedidolog'>" . htmlspecialchars($line) . "</span>";
    } elseif (stripos($line, 'error') !== false) {
        echo "<span class='error'>" . htmlspecialchars($line) . "</span>";
    } elseif (stripos($line, 'warn') !== false) {
        echo "<span class='warn'>" . htmlspecialchars($line) . "</span>";
    } else {
        echo "<span class='info'>" . htmlspecialchars($line) . "</span>";
    }
}
echo "</pre>
    <form method='post'><button type='submit' name='clear' style='margin-top:1em;'>Limpiar log</button></form>
</body>
</html>";

// Opción para limpiar el log desde la web (¡Úsalo solo en desarrollo!)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
    file_put_contents($log_path, '');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$logFile = __DIR__ . '/logs/errores.log';
if (file_exists($logFile)) {
    $errores = file($logFile);
    echo "<h2>Errores registrados</h2><pre>";
    foreach ($errores as $linea) {
        echo htmlspecialchars($linea);
    }
    echo "</pre>";
} else {
    echo "No hay errores registrados.";
}
?> 