<?php
// get_playlist.php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

// Récupérer l'identifiant unique de l'appareil envoyé par l'APK
$device_uid = isset($_GET['device_uid']) ? $_GET['device_uid'] : '';

if (empty($device_uid)) {
    echo json_encode(["status" => "error", "message" => "Device UID manquant"]);
    exit;
}

try {
    // 1. Vérifier / Enregistrer l'appareil et mettre à jour son 'last_seen'
    $stmt = $pdo->prepare("INSERT INTO devices (device_name, device_uid) VALUES ('Kakémono Principal', ?) 
                           ON DUPLICATE KEY UPDATE last_seen = CURRENT_TIMESTAMP");
    $stmt->execute([$device_uid]);

    // Récupérer l'ID interne de l'appareil
    $stmt = $pdo->prepare("SELECT id FROM devices WHERE device_uid = ?");
    $stmt->execute([$device_uid]);
    $device = $stmt->fetch();
    $device_id = $device['id'];

    // 2. Récupérer la playlist associée à cet appareil
    $stmt = $pdo->prepare("
        m.id, m.title, m.file_path, m.file_type, m.duration 
        FROM playlists p
        JOIN media m ON p.media_id = m.id
        WHERE p.device_id = ?
        ORDER BY p.item_order ASC
    ");
    $stmt->execute([$device_id]);
    $playlistItems = $stmt->fetchAll();

    // Formater l'URL complète des fichiers
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/";

    $formattedPlaylist = [];
    foreach ($playlistItems as $item) {
        $formattedPlaylist[] = [
            "id" => (int)$item['id'],
            "type" => $item['file_type'],
            "url" => $baseUrl . $item['file_path'],
            "duration" => (int)$item['duration']
        ];
    }

    echo json_encode([
        "status" => "success",
        "sync_interval" => 300,
        "playlist" => $formattedPlaylist
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>