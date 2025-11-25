<?php
require_once 'Rol_model.php';
require_once '../model/usermodel.php';
require_once '../config/conexion.php';

class UserModel extends Rol_model
{
    public function __construct($pdo)
    {
        parent::__construct($pdo); // hereda la conexión de RolModel
    }

    // Buscar usuario por username o email
    public function getUserByUsernameOrEmail($usernameOrEmail)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, a.id_agricultor
            FROM usuarios u
            LEFT JOIN agricultor a ON u.id_usuario = a.id_usuario
            WHERE u.username = ? OR u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Agregar usuario
public function addUser(
    $nombre_completo,
    $tipo_documento,
    $numero_documento,
    $telefono,
    $email,
    $fecha_nacimiento,
    $username,
    $password,
    $id_rol,
    $foto
) {
    $stmt = $this->pdo->prepare("
        INSERT INTO usuarios 
            (nombre_completo, tipo_documento, numero_documento, telefono, email, fecha_nacimiento, username, password, id_rol, foto) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt->execute([
        $nombre_completo,
        $tipo_documento,
        $numero_documento,
        $telefono,
        $email,
        $fecha_nacimiento,
        $username,
        $password,
        $id_rol,
        $foto
    ])) {
        return $this->pdo->lastInsertId();
    }

    return false;
}

    // Actualizar usuario
    public function updateUser(
        $id_usuario,
        $nombre_completo,
        $tipo_documento,
        $numero_documento,
        $telefono,
        $email,
        $fecha_nacimiento,
        $username,
        $password,
        $id_rol
    ) {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios SET 
                nombre_completo = ?,
                tipo_documento = ?, 
                numero_documento = ?, 
                telefono = ?, 
                email = ?, 
                fecha_nacimiento = ?, 
                username = ?, 
                password = ?, 
                id_rol = ?
            WHERE id_usuario = ?
        ");

        return $stmt->execute([
            $nombre_completo,
            $tipo_documento,
            $numero_documento,
            $telefono,
            $email,
            $fecha_nacimiento,
            $username,
            $password,
            $id_rol,
            $id_usuario
        ]);
    }

    // Eliminar usuario
    public function deleteUser($id_usuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        return $stmt->execute([$id_usuario]);
    }

    public function storeResetToken(string $email, string $tokenHash, string $expiresAt): bool
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (
            email VARCHAR(255),
            token VARCHAR(255),
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$email]);

        $insert = $this->pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
        return $insert->execute([$email, $tokenHash, $expiresAt]);
    }

    public function getResetRequest(string $tokenHash)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM password_resets WHERE token = ? LIMIT 1');
        $stmt->execute([$tokenHash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteResetToken(string $tokenHash): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE token = ?');
        $stmt->execute([$tokenHash]);
    }

    public function updatePasswordByEmail(string $email, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET password = ? WHERE email = ?');
        return $stmt->execute([$hashedPassword, $email]);
    }

    // Obtener usuario con su rol
    public function getUserWithRole($id_usuario)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = ?
        ");
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
