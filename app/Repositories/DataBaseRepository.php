<?php

namespace App\Repositories;

use PDO;

class DataBaseRepository
{
    private ?PDO $db = null;

    public function getApiKey(string $email): ?string
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

    public function getTokenFromDataBase(string $email): ?array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('SELECT api_key, email, token, fechaexpiracion FROM usuario WHERE email = :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateUserTokenInDataBase(array $nuevoToken, $email1): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'UPDATE usuario SET token = :token, fechaexpiracion = :fechaExpiracion WHERE email = :email'
        );
        $stmt->bindValue(':token', $nuevoToken['token'], PDO::PARAM_STR);
        $stmt->bindValue(':fechaExpiracion', $nuevoToken['expiracion'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $email1, PDO::PARAM_STR);
        $stmt->execute();
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
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':login', $data['login']);
        $stmt->bindParam(':display_name', $data['display_name']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':broadcaster_type', $data['broadcaster_type']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':profile_image_url', $data['profile_image_url']);
        $stmt->bindParam(':offline_image_url', $data['offline_image_url']);
        $stmt->bindParam(':view_count', $data['view_count'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);

        $stmt->execute();
    }

    public function getApiTokenFromDataBase(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('SELECT * FROM token_twitch LIMIT 1');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return [strtotime($result['expiracion']), $result['token']] ?: [0, null];
    }

    public function updateApiTokenInDataBase(array $data): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('UPDATE token_twitch SET token = :token, expiracion = :expiracion');
        $stmt->bindValue(':token', $data['access_token']);
        $stmt->bindValue(':expiracion', date('Y-m-d H:i:s', time() + $data['expires_in']));
        $stmt->execute();
    }

    public function insertApiTokenInDataBase(array $data): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('INSERT INTO token_twitch (token, expiracion) VALUES (:token, :expiracion)');
        $stmt->bindValue(':token', $data['access_token']);
        $stmt->bindValue(':expiracion', date('Y-m-d H:i:s', time() + $data['expires_in']));
        $stmt->execute();
    }

    public function getTokenExpirationDateFromDataBase(string $token): int|null
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('SELECT fechaexpiracion FROM usuario WHERE token = :token');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['fechaexpiracion']) ? strtotime($result['fechaexpiracion']) : null;
    }

    public function getUltimaSolicitud(): int
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('SELECT ultima_solicitud FROM cache ORDER BY ultima_solicitud DESC LIMIT 1');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return strtotime($result['ultima_solicitud'] ?? '0');
    }

    public function getTops(): array
    {
        $pdo = $this->getConnection();

        return $pdo->query('SELECT GAME_NAME, USER_NAME FROM CACHE GROUP BY GAME_NAME, USER_NAME')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerInformacionJuego(string $game_name, string $user_name): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'WITH MaxViews AS (
                SELECT GAME_ID, GAME_NAME, USER_NAME, TITLE, VIEWS, DURATION, CREATED_AT,
                       ROW_NUMBER() OVER (PARTITION BY GAME_ID, USER_NAME ORDER BY VIEWS DESC) AS row_num
                FROM CACHE)
            SELECT c.GAME_ID, c.GAME_NAME, c.USER_NAME, COUNT(*) AS TOTAL_VIDEOS, 
                   SUM(c.VIEWS) AS TOTAL_VIEWS,
                   mv.TITLE AS MOST_VIEWED_TITLE, mv.VIEWS AS MOST_VIEWED_VIEWS,
                   mv.DURATION AS MOST_VIEWED_DURATION, mv.CREATED_AT AS MOST_VIEWED_CREATED_AT
            FROM CACHE c
            JOIN MaxViews mv ON c.GAME_ID = mv.GAME_ID AND c.USER_NAME = mv.USER_NAME AND mv.row_num = 1
            WHERE c.GAME_NAME = :game_name AND c.USER_NAME = :user_name
            GROUP BY c.GAME_ID, c.GAME_NAME, c.USER_NAME, mv.TITLE, mv.VIEWS, mv.DURATION, mv.CREATED_AT
            ORDER BY TOTAL_VIEWS DESC'
        );

        $stmt->bindValue(':game_name', $game_name);
        $stmt->bindValue(':user_name', $user_name);
        $stmt->execute();

        $final = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $final[] = array_map('strval', $row);
        }

        return $final;
    }

    public function clearCache()
    {
        $pdo = $this->getConnection();

        $pdo->exec('DELETE FROM cache');
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
