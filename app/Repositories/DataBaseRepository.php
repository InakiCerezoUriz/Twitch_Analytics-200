<?php

namespace TwitchAnalytics\Repositories;

use PDO;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Models\TopStreamer;

class DataBaseRepository implements DataBaseRepositoryInterface
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

    public function insertarTopStreamer(TopStreamer $topStreamer): void
    {
        $pdo = $this->getConnection();

        $topStreamer = $topStreamer->getTopStreamer();

        $stmt = $pdo->prepare(
            'INSERT INTO CACHE (
                GAME_ID, GAME_NAME, USER_NAME, TOTAL_VIDEOS, TOTAL_VIEWS,
                TITLE, VIEWS, DURATION, CREATED_AT, ULTIMA_SOLICITUD
            )
            VALUES (
                :game_id, :game_name, :user_name, :total_videos, :total_views,
                :title, :views, :duration, :created_at, :ultima_solicitud
            )'
        );
        $stmt->bindValue(':game_id', $topStreamer['game_id']);
        $stmt->bindValue(':game_name', $topStreamer['game_name']);
        $stmt->bindValue(':user_name', $topStreamer['user_name']);
        $stmt->bindValue(':total_videos', $topStreamer['total_videos'], PDO::PARAM_INT);
        $stmt->bindValue(':total_views', $topStreamer['total_views'], PDO::PARAM_INT);
        $stmt->bindValue(':title', $topStreamer['most_viewed_title']);
        $stmt->bindValue(':views', $topStreamer['most_viewed_views'], PDO::PARAM_INT);
        $stmt->bindValue(':duration', $topStreamer['most_viewed_duration'], PDO::PARAM_INT);
        $stmt->bindValue(':created_at', $topStreamer['most_viewed_created_at']);
        $stmt->bindValue(':ultima_solicitud', date('Y-m-d H:i:s'));

        $stmt->execute();
    }

    public function getTopStreamer(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('SELECT 
                GAME_ID,
                GAME_NAME,
                USER_NAME,
                TOTAL_VIDEOS,
                TOTAL_VIEWS,
                TITLE AS most_viewed_title,
                VIEWS AS most_viewed_views,
                DURATION AS most_viewed_duration,
                CREATED_AT AS most_viewed_created_at
            FROM cache');

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearCache(): void
    {
        $pdo = $this->getConnection();

        $pdo->exec('DELETE FROM cache');
    }

    /**
     * @return PDO
     */
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
