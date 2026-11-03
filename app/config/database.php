<?php
class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            $host = 'localhost';
            $dbname = 'kakemono_db';
            $username = 'root';
            $password = '';

            try {
                self::$instance = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                // En production, logger l'erreur au lieu de l'afficher brutalement
                die("Erreur de connexion sécurisée à la base de données.");
            }
        }
        return self::$instance;
    }
}