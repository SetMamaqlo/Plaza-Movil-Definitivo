<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once __DIR__ . '/config/session_timeout.php';
if (!isset($_SESSION['user_id_usuario'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';
$user_id_usuario = $_SESSION['user_id_usuario'];
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
$stmt->execute([$user_id_usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo '<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">Usuario no encontrado.</div>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/css/styles.css">
</head>
<body class="bg-gradient-to-b from-emerald-50 to-white min-h-screen">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-4xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <img src="<?php echo !empty($user['Foto']) ? '../img/' . htmlspecialchars($user['Foto']) : '../img/default_profile.png'; ?>"
                    alt="Foto de perfil" class="h-32 w-32 rounded-full object-cover mx-auto mb-4 ring-4 ring-emerald-100">
                <h1 class="text-3xl font-bold text-slate-900"><?php echo htmlspecialchars($user['nombre_completo']); ?></h1>
                <p class="text-slate-600">@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div class="mt-8">
                <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="bi bi-person-lines-fill me-2"></i>Información Personal</h2>
                <div class="space-y-3">
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100">
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100">
                        <strong>Teléfono:</strong> <?php echo htmlspecialchars($user['telefono'] ?? 'No disponible'); ?>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100">
                        <strong>Dirección:</strong> <?php echo htmlspecialchars($user['direccion'] ?? 'No disponible'); ?>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <button class="rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-3 transition" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                    <i class="bi bi-pencil me-2"></i>Editar Perfil
                </button>
                <a href="historialpedidos.php" class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 transition inline-block">
                    <i class="bi bi-clock-history me-2"></i>Historial de Pedidos
                </a>
                <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 transition" data-bs-toggle="modal" data-bs-target="#modalEliminarPerfil">
                    <i class="bi bi-trash me-2"></i>Eliminar Perfil
                </button>
            </div>

            <hr class="my-8 border-slate-200">

            <!-- Historial de pedidos -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="bi bi-clock-history me-2"></i>Historial de Pedidos Recientes</h2>
                <?php
                $stmtPedidos = $pdo->prepare("SELECT id_pedido, fecha, estado FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 5");
                $stmtPedidos->execute([$user_id_usuario]);
                $pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);

                if ($pedidos && count($pedidos) > 0) {
                    foreach ($pedidos as $pedido) {
                        ?>
                        <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-4 mb-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <strong>Pedido #<?php echo htmlspecialchars($pedido['id_pedido']); ?></strong>
                                    <span class="text-sm text-slate-600 ms-3">Fecha: <?php echo htmlspecialchars($pedido['fecha']); ?></span>
                                </div>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-emerald-200 text-emerald-800">
                                    <?php echo htmlspecialchars($pedido['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="rounded-lg bg-blue-50 border border-blue-100 p-4 text-blue-700"><i class="bi bi-info-circle me-2"></i>No tienes pedidos recientes.</div>';
                }
                ?>
            </div>

            <hr class="my-8 border-slate-200">

            <!-- Mis PQRS -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="bi bi-chat-dots me-2"></i>Mis PQRS Recientes</h2>
                <?php
                $stmtPQRS = $pdo->prepare("SELECT asunto, estado, fecha, respuesta FROM pqrs WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 5");
                $stmtPQRS->execute([$user_id_usuario]);
                $pqrs = $stmtPQRS->fetchAll(PDO::FETCH_ASSOC);

                if ($pqrs && count($pqrs) > 0) {
                    foreach ($pqrs as $pq) {
                        ?>
                        <div class="rounded-lg bg-yellow-50 border border-yellow-100 p-4 mb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <strong><?php echo htmlspecialchars($pq['asunto']); ?></strong>
                                    <p class="text-sm text-slate-600 mt-1">Fecha: <?php echo htmlspecialchars($pq['fecha']); ?></p>
                                    <?php if (!empty($pq['respuesta'])): ?>
                                        <p class="text-sm mt-2 text-green-700"><i class="bi bi-check-circle me-1"></i>Respuesta recibida</p>
                                    <?php else: ?>
                                        <p class="text-sm mt-2 text-yellow-700"><i class="bi bi-hourglass-split me-1"></i>Pendiente de respuesta</p>
                                    <?php endif; ?>
                                </div>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-yellow-200 text-yellow-800">
                                    <?php echo htmlspecialchars($pq['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="rounded-lg bg-blue-50 border border-blue-100 p-4 text-blue-700"><i class="bi bi-info-circle me-2"></i>No tienes PQRS registradas.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal editar perfil -->
    <div id="modalEditarPerfil" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900">Editar Perfil</h3>
                <button onclick="document.getElementById('modalEditarPerfil').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
            </div>
            <form action="../controller/editarperfilcontroller.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario</label>
                    <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Foto de Perfil</label>
                    <input type="file" name="foto_perfil" accept="image/*" class="w-full">
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('modalEditarPerfil').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal eliminar perfil -->
    <div id="modalEliminarPerfil" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Eliminar Perfil</h3>
            <p class="text-slate-600 mb-6">¿Estás seguro? Esta acción no se puede deshacer.</p>
            <form action="../controller/eliminarperfilcontroller.php" method="POST">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirma tu contraseña:</label>
                    <input type="password" name="confirm_password" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('modalEliminarPerfil').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Manejo de modales simples sin Bootstrap
        document.addEventListener('click', function(e) {
            if (e.target.hasAttribute('data-bs-toggle') && e.target.getAttribute('data-bs-toggle') === 'modal') {
                const modalId = e.target.getAttribute('data-bs-target').substring(1);
                document.getElementById(modalId).classList.remove('hidden');
            }
        });
    </script>
</body>
</html>