<?php
class MediaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAllMedias() {
        $stmt = $this->db->query("SELECT * FROM media ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function createMedia($title, $filePath, $fileType, $duration) {
        $stmt = $this->db->prepare("INSERT INTO media (title, file_path, file_type, duration) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $filePath, $fileType, $duration]);
        return $this->db->lastInsertId();
    }

    public function assignToDefaultDevice($mediaId) {
        // Associer automatiquement au premier kakémono enregistré
        $stmtDev = $this->db->query("SELECT id FROM devices LIMIT 1");
        $device = $stmtDev->fetch();
        
        if ($device) {
            $stmt = $this->db->prepare("
                INSERT INTO playlists (device_id, media_id, item_order) 
                VALUES (?, ?, (SELECT IFNULL(MAX(item_order), 0) + 1 FROM playlists p2 WHERE p2.device_id = ?))
            ");
            $stmt->execute([$device['id'], $mediaId, $device['id']]);
        }
    }
}