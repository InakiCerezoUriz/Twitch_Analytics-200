<?php

namespace App\Repositories;

use PDO;

class DataBaseRepository
{
    private ?PDO $db = null;

    public function getApiKey(string $email)
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('SELECT api_key FROM usuario WHERE email = :email');
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['api_key'] ?? null;
    }

    public function updateApiKey(string $apiKey, string $email): void
    {
        $pdo = $this->getConnection();

        $updateStmt = $pdo->prepare('UPDATE usuario SET api_key = :api_key WHERE email = :email');
        $updateStmt->bindValue(':api_key', $apiKey, PDO::PARAM_STR);
        $updateStmt->bindValue(':email', $email, PDO::PARAM_STR);
        $updateStmt->execute();
    }

    public function insertApiKey(string $email, mixed $apiKey): void
    {
        $pdo = $this->getConnection();

        $insertStmt = $pdo->prepare('INSERT INTO usuario (email, api_key) VALUES (:email, :api_key)');
        $insertStmt->bindValue(':email', $email, PDO::PARAM_STR);
        $insertStmt->bindValue(':api_key', $apiKey, PDO::PARAM_STR);
        $insertStmt->execute();
    }

    public function getUserFromDataBase(string $id): ?array
    {
        $pdo = $this->getConnection();

        $sql = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $sql->bindParam(':id', $id);
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function insertUserInDataBase($data): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'INSERT INTO users (
                                        id, login, display_name, type, broadcaster_type, description, 
                                        profile_image_url, offline_image_url, view_count, created_at
                                    ) VALUES (
                                        :id, :login, :display_name, :type, :broadcaster_type, :description, 
                                        :profile_image_url, :offline_image_url, :view_count, :created_at
                                    )'
        );
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_STR);
        $stmt->bindParam(':login', $data['login'], PDO::PARAM_STR);
        $stmt->bindParam(':display_name', $data['display_name'], PDO::PARAM_STR);
        $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
        $stmt->bindParam(':broadcaster_type', $data['broadcaster_type'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':profile_image_url', $data['profile_image_url'], PDO::PARAM_STR);
        $stmt->bindParam(':offline_image_url', $data['offline_image_url'], PDO::PARAM_STR);
        $stmt->bindParam(':view_count', $data['view_count'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at'], PDO::PARAM_STR);

        $stmt->execute();
    }


    private function getConnection(): PDO
    {
        if ($this->db === null) {
            $this->db = $this->connect();
        }

        return $this->db;
    }

    public function connect(): ?PDO
    {
        $host     = env('DB_HOST');
        $port     = env('DB_PORT');
        $dbname   = env('DB_DATABASE');
        $user     = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        try {
            $pdo = new PDO($dsn, $user, $password);
            // Set error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Conexión exitosa
            return $pdo;
        } catch (\PDOException $e) {
            // Error de conexión
            error_log('Connection failed: ' . $e->getMessage());
            return null;
        }
    }
}
