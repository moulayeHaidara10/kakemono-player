<?php
class ApiController {

    public function getPlaylist() {
        header('Content-Type: application/json; charset=utf-8');
        
        $device_uid = $_GET['device_uid'] ?? '';
        if (empty($device_uid)) {
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        $db = Database::getConnection();

        // Enregistrer / Mettre à jour l'appareil
        $stmt = $db->prepare("INSERT INTO devices (device_name, device_uid) VALUES ('Kakemono Principal', ?) 
                               ON DUPLICATE KEY UPDATE last_seen = CURRENT_TIMESTAMP");
        $stmt->execute([$device_uid]);

        $stmt = $db->prepare("SELECT id FROM devices WHERE device_uid = ?");
        $stmt->execute([$device_uid]);
        $device = $stmt->fetch();
        
        if (!$device) {
            echo json_encode(["status" => "error", "message" => "Device not found"]);
            exit;
        }

        // Récupérer la playlist
        $stmt = $db->prepare("
            SELECT m.id, m.title, m.file_path, m.file_type, m.duration 
            FROM playlists p
            JOIN media m ON p.media_id = m.id
            WHERE p.device_id = ?
            ORDER BY p.item_order ASC
        ");
        $stmt->execute([$device['id']]);
        $items = $stmt->fetchAll();

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/";
        // Nettoyer l'URL de base pour pointer sur /public/
        $baseUrl = str_replace('/public', '', $baseUrl) . 'public/';

        $playlist = [];
        foreach ($items as $item) {
            $playlist[] = [
                "id" => (int)$item['id'],
                "type" => $item['file_type'],
                "url" => $baseUrl . $item['file_path'],
                "duration" => (int)$item['duration']
            ];
        }

        echo json_encode([
            "status" => "success",
            "sync_interval" => 300,
            "playlist" => $playlist
        ]);
    }
}