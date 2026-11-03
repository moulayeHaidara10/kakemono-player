<?php
class DashboardController {

    public function index() {
        $mediaModel = new MediaModel();
        $medias = $mediaModel->getAllMedias();
        
        // Charger la vue
        require_once __DIR__ . '/../views/admin/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
            $title = trim($_POST['title'] ?? '');
            $duration = intval($_POST['duration'] ?? 10);
            
            if (empty($title) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
                header('Location: index.php?action=dashboard&error=1');
                exit;
            }

            // Validation stricte du type MIME réel du fichier
            $fileTmpPath = $_FILES['media_file']['tmp_name'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fileTmpPath);

            $allowedMimes = [
                'image/jpeg' => 'image',
                'image/png'  => 'image',
                'image/webp' => 'image',
                'video/mp4'  => 'video'
            ];

            if (!array_key_exists($mimeType, $allowedMimes)) {
                die("Type de fichier non autorisé pour des raisons de sécurité.");
            }

            $fileType = $allowedMimes[$mimeType];
            
            // Renommer le fichier de manière unique pour éviter les collisions et injections
            $extension = ($fileType === 'image') ? '.jpg' : '.mp4';
            if($fileType === 'image') {
                $extension = '.' . pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION);
            }
            
            $newFileName = md5(time() . rand()) . strtolower($extension);
            $uploadFileDir = __DIR__ . '/../../public/uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            $dbPath = 'uploads/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $mediaModel = new MediaModel();
                $mediaId = $mediaModel->createMedia($title, $dbPath, $fileType, $duration);
                $mediaModel->assignToDefaultDevice($mediaId);
            }
        }

        header('Location: index.php?action=dashboard');
        exit;
    }
}