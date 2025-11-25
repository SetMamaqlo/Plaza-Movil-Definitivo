<?php
require_once '../config/auth.php';
require_once '../model/productmodel.php';
require_once '../config/conexion.php';

class ProductController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new ProductModel($pdo);
    }

 private function uploadImage(string $inputName): ?string
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$inputName];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if ($file['size'] > $maxSize) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!array_key_exists($mime, $allowedMime)) {
            return null;
        }

        $extension = $allowedMime[$mime];
        $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = uniqid('img_', true) . '_' . $safeName . '.' . $extension;

        $destDir = realpath(__DIR__ . '/../img');
        if ($destDir === false) {
            return null;
        }

        $targetPath = $destDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return null;
        }

        return $filename;
    }

    public function addProduct() {
        requireRole([ROLE_AGRICULTOR]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_agricultor = $_SESSION['user_id_agricultor'] ?? null;

            if (!$id_agricultor && isset($_SESSION['user_id_usuario'])) {
                // Buscar agricultor desde DB
                $stmt = $this->pdo->prepare("SELECT id_agricultor FROM agricultor WHERE id_usuario = ?");
                $stmt->execute([$_SESSION['user_id_usuario']]);
                $agricultor = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($agricultor) {
                    $id_agricultor = $agricultor['id_agricultor'];
                    $_SESSION['user_id_agricultor'] = $id_agricultor;
                }
            }

            error_log("SESSION: " . json_encode($_SESSION));

            if (!$id_agricultor) {
                echo "<script>alert('Error: No se encontró el agricultor.'); window.location.href='../view/mis_productos.php';</script>";
                return;
            }

            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $precio = $_POST['precio_unitario'];
            $stock = $_POST['stock'];
            $id_categoria = $_POST['id_categoria'];
            $id_unidad = $_POST['id_unidad'];
            $fecha_publicacion = date("Y-m-d H:i:s");

            $foto = $this->uploadImage('foto');

            if (!empty($_FILES['foto']['name']) && $foto === null) {
                echo "<script>alert('La imagen debe ser JPG, PNG o WEBP y pesar menos de 2MB.'); window.location.href='../view/mis_productos.php';</script>";
                return;
            }

            if ($this->model->addProduct($nombre, $descripcion, $precio, $foto, $id_agricultor, $stock, $id_categoria, $id_unidad, $fecha_publicacion)) {
                echo "<script>alert('¡Producto añadido con éxito!'); window.location.href='../view/mis_productos.php?add=ok';</script>";
            } else {
                echo "<script>alert('Error al añadir el producto.'); window.location.href='../view/mis_productos.php';</script>";
            }
        }
    }
}

$controller = new ProductController($pdo);
$controller->addProduct();