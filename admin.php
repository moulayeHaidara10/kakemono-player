<?php
// index.php
require_once 'db.php';

$message = "";

// Traitement de l'upload d'un média
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $title = $_POST['title'] ?? 'Sans titre';
    $duration = intval($_POST['duration'] ?? 10);
    
    // Dossier de destination des fichiers
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = basename($_FILES['media_file']['name']);
    $targetFilePath = $uploadDir . time() . "_" . $fileName;
    $fileTypeExt = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Déterminer si c'est une image ou une vidéo
    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $videoExtensions = ['mp4', 'mkv', 'avi', 'mov'];

    if (in_array($fileTypeExt, $imageExtensions)) {
        $fileType = 'image';
    } elseif (in_array($fileTypeExt, $videoExtensions)) {
        $fileType = 'video';
    } else {
        $message = "Format de fichier non supporté.";
        $fileType = '';
    }

    if (!empty($fileType) && move_uploaded_file($_FILES['media_file']['tmp_name'], $targetFilePath)) {
        // Enregistrer en base de données
        $stmt = $pdo->prepare("INSERT INTO media (title, file_path, file_type, duration) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $targetFilePath, $fileType, $duration]);
        
        // Optionnel : L'ajouter automatiquement à la playlist du premier appareil par défaut
        $mediaId = $pdo->lastInsertId();
        $stmtDev = $pdo->query("SELECT id FROM devices LIMIT 1");
        $device = $stmtDev->fetch();
        if ($device) {
            $stmtPlaylist = $pdo->prepare("INSERT INTO playlists (device_id, media_id, item_order) VALUES (?, ?, (SELECT IFNULL(MAX(item_order), 0) + 1 FROM playlists p2 WHERE p2.device_id = ?))");
            $stmtPlaylist->execute([$device['id'], $mediaId, $device['id']]);
        }

        $message = "Média ajouté et synchronisé avec succès !";
    } else {
        if(empty($message)) $message = "Erreur lors du téléchargement du fichier.";
    }
}

// Récupérer la liste des médias existants
$medias = $pdo->query("SELECT * FROM media ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Kakemono - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f9; color: #333; }
        .container { max-width: 800px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, button { padding: 10px; margin: 5px 0 15px 0; width: 100%; box-sizing: border-box; }
        button { background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .msg { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Tableau de bord - Gestion du Kakémono</h2>
    
    <?php if (!empty($message)): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Titre du média :</label>
        <input type="text" name="title" required placeholder="Ex: Promo Été 2026">

        <label>Fichier (Image ou Vidéo MP4) :</label>
        <input type="file" name="media_file" required>

        <label>Durée d'affichage (Secondes - uniquement pour les images) :</label>
        <input type="number" name="duration" value="10" min="1">

        <button type="submit">Uploader et envoyer au Kakémono</button>
    </form>

    <h3>Médias enregistrés sur le serveur</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Aperçu / Titre</th>
            <th>Type</th>
            <th>Durée</th>
        </tr>
        <?php foreach ($medias as $m): ?>
        <tr>
            <td><?= $m['id'] ?></td>
            <td><?= htmlspecialchars($m['title']) ?><br><small><?= $m['file_path'] ?></small></td>
            <td><?= $m['file_type'] ?></td>
            <td><?= $m['duration'] ?>s</td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>