<?php
// public/index.php
session_start();

// Autoload simple des classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/config/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Récupération de la route demandée
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case 'upload':
        $controller = new DashboardController();
        $controller->store();
        break;

    case 'api_playlist':
        $controller = new ApiController();
        $controller->getPlaylist();
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        break;
}