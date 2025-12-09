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

$stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, email, username, id_rol FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = isset($_GET['success']) && $_GET['success'] == 1;
$deleted = isset($_GET['deleted']) && $_GET['deleted'] == 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Gestión de Usuarios</h1>
                <a href="dashboard.php" class="rounded-xl border border-slate-200 text-slate-700 px-6 py-2 font-semibold hover:bg-slate-50">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>

            <div class="mb-8">
                <button type="button" onclick="document.getElementById('crearUsuarioModal').classList.remove('hidden')" 
                        class="rounded-xl bg-emerald-600 text-white px-6 py-2 font-semibold hover:bg-emerald-500">
                    <i class="bi bi-plus-circle me-2"></i>Crear Usuario
                </button>
            </div>

            <!-- Modal crear usuario -->
            <div id="crearUsuarioModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-slate-900">Crear Nuevo Usuario</h3>
                        <button onclick="document.getElementById('crearUsuarioModal').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
                    </div>
                    <form action="../controller/crear_usuario.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre Completo</label>
                            <input type="text" name="nombre_completo" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Contraseña</label>
                            <input type="password" name="password" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre de Usuario</label>
                            <input type="text" name="username" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rol</label>
                            <select name="rol" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                                <option value="1">Administrador</option>
                                <option value="2">Vendedor</option>
                                <option value="3">Comprador</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('crearUsuarioModal').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                            <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Crear Usuario</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-emerald-50 border-b-2 border-emerald-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Nombre Completo</th>
                            <th class="px-4 py-3 text-left font-semibold">Email</th>
                            <th class="px-4 py-3 text-left font-semibold">Rol</th>
                            <th class="px-4 py-3 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3"><?= htmlspecialchars($usuario['id_usuario']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($usuario['nombre_completo']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($usuario['email']); ?></td>
                                <td class="px-4 py-3"><?= $usuario['id_rol'] == 1 ? 'Administrador' : ($usuario['id_rol'] == 2 ? 'Vendedor' : 'Comprador'); ?></td>
                                <td class="px-4 py-3 text-center space-x-2">
                                    <button type="button" onclick="document.getElementById('modalEditar<?= $usuario['id_usuario']; ?>').classList.remove('hidden')" class="rounded-lg bg-amber-500 text-white px-3 py-1 text-xs font-semibold hover:bg-amber-600">
                                        <i class="bi bi-pencil"></i>Editar
                                    </button>
                                    <form action="../controller/eliminar_usuario.php" method="POST" class="inline">
                                        <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario']; ?>">
                                        <button type="submit" class="rounded-lg bg-red-600 text-white px-3 py-1 text-xs font-semibold hover:bg-red-700" onclick="return confirm('¿Estás seguro?');">
                                            <i class="bi bi-trash"></i>Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal editar usuario -->
                            <div id="modalEditar<?= $usuario['id_usuario']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
                                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-xl font-bold text-slate-900">Editar Usuario</h3>
                                        <button onclick="document.getElementById('modalEditar<?= $usuario['id_usuario']; ?>').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
                                    </div>
                                    <form action="../controller/editar_usuario.php" method="POST" class="space-y-4">
                                        <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario']; ?>">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre Completo</label>
                                            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($usuario['nombre_completo']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                                            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rol</label>
                                            <select name="rol" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                                                <option value="1" <?= $usuario['id_rol'] == 1 ? 'selected' : ''; ?>>Administrador</option>
                                                <option value="2" <?= $usuario['id_rol'] == 2 ? 'selected' : ''; ?>>Vendedor</option>
                                                <option value="3" <?= $usuario['id_rol'] == 3 ? 'selected' : ''; ?>>Comprador</option>
                                            </select>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" onclick="document.getElementById('modalEditar<?= $usuario['id_usuario']; ?>').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                                            <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
