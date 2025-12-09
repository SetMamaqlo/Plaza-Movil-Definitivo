<?php
require_once __DIR__ . '/../config/database.php';

class NotificacionModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS notificaciones (
            id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            mensaje VARCHAR(255) NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            leida TINYINT(1) DEFAULT 0,
            creada_en DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->pdo->exec($sql);
    }

    public function crear(int $idUsuario, string $mensaje, ?string $link = null): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, link) VALUES (?, ?, ?)");
        $stmt->execute([$idUsuario, $mensaje, $link]);
    }

    public function obtenerNoLeidas(int $idUsuario, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("SELECT id_notificacion, mensaje, link, creada_en FROM notificaciones WHERE id_usuario = ? AND leida = 0 ORDER BY creada_en DESC LIMIT ?");
        $stmt->bindValue(1, $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function contarNoLeidas(int $idUsuario): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = 0");
        $stmt->execute([$idUsuario]);
        return (int) $stmt->fetchColumn();
    }

    public function marcarTodasLeidas(int $idUsuario): void
    {
        $stmt = $this->pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
    }
}
