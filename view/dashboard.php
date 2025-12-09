<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/../config/session_timeout.php';

$id_rol = isset($_SESSION['user_id_rol']) ? (int) $_SESSION['user_id_rol'] : null;

if ($id_rol !== 1) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrador - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Dashboard Administrativo</h1>
            <p class="text-slate-600">Bienvenido al panel de control. Gestiona todos los aspectos de Plaza Móvil desde aquí.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Gestión de Usuarios -->
            <a href="gestion_usuarios.php"
               class="group rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 overflow-hidden transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-6 text-white">
                    <i class="bi bi-people-fill text-4xl mb-2 block"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Gestión de Usuarios</h3>
                    <p class="text-sm text-slate-600 mb-4">Administra los usuarios registrados en el sistema.</p>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 group-hover:gap-3 transition">
                        Ir <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>

            <!-- Gestión de Productos -->
            <a href="gestion_productos.php"
               class="group rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 overflow-hidden transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-6 text-white">
                    <i class="bi bi-box-seam text-4xl mb-2 block"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Gestión de Productos</h3>
                    <p class="text-sm text-slate-600 mb-4">Administra los productos publicados en el sistema.</p>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 group-hover:gap-3 transition">
                        Ir <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>

            <!-- Gestión de Unidades de Medida -->
            <a href="gestion_medidas.php"
               class="group rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 overflow-hidden transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="bg-gradient-to-br from-red-400 to-red-600 p-6 text-white">
                    <i class="bi bi-scale text-4xl mb-2 block"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Gestión de Medidas</h3>
                    <p class="text-sm text-slate-600 mb-4">Administra las unidades de medida del sistema.</p>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 group-hover:gap-3 transition">
                        Ir <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>

            <!-- Gestión de Categorías -->
            <a href="gestion_categorias.php"
               class="group rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 overflow-hidden transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="bg-gradient-to-br from-amber-400 to-amber-600 p-6 text-white">
                    <i class="bi bi-tags-fill text-4xl mb-2 block"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Gestión de Categorías</h3>
                    <p class="text-sm text-slate-600 mb-4">Administra las categorías disponibles en el sistema.</p>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-amber-600 group-hover:gap-3 transition">
                        Ir <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>

            <!-- Gestión de PQRS -->
            <a href="admin_pqrs.php"
               class="group rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 overflow-hidden transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="bg-gradient-to-br from-cyan-400 to-cyan-600 p-6 text-white">
                    <i class="bi bi-chat-dots-fill text-4xl mb-2 block"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Gestión de PQRS</h3>
                    <p class="text-sm text-slate-600 mb-4">Revisa y responde las PQRS de los usuarios.</p>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-600 group-hover:gap-3 transition">
                        Ir <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>

        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
