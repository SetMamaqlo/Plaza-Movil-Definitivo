<?php
session_start();
require_once '../model/pqrs_model.php';

if (!isset($_SESSION['user_id_usuario']) || $_SESSION['user_id_rol'] != 1) {
    header('Location: login.php');
    exit();
}

$pqrs = PQRSModel::obtenerTodas();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pqrs'], $_POST['respuesta'])) {
    PQRSModel::responder($_POST['id_pqrs'], $_POST['respuesta']);
    header("Location: admin_pqrs.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión PQRS - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Gestión de PQRS</h1>
            <p class="text-slate-600">Revisa y responde a todas las PQRS (Peticiones, Quejas, Reclamos y Sugerencias) de los usuarios.</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                <i class="bi bi-check-circle me-2"></i> Respuesta registrada correctamente.
            </div>
        <?php endif; ?>

        <?php if (empty($pqrs)): ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8 text-center">
                <i class="bi bi-inbox text-6xl text-slate-300 mb-4 block"></i>
                <p class="text-lg text-slate-600">No hay PQRS registradas en el sistema.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($pqrs as $item): ?>
                    <div class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-cyan-50 to-blue-50 px-6 py-4 border-b border-slate-200">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($item['asunto']); ?></h3>
                                    <p class="text-sm text-slate-600 mt-1">
                                        <i class="bi bi-person me-1"></i> <?= htmlspecialchars($item['nombre_completo']); ?> • 
                                        <i class="bi bi-calendar me-1"></i> <?= htmlspecialchars($item['fecha']); ?>
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($item['tipo']); ?>
                                    </span>
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?= $item['estado'] === 'respondido' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?= htmlspecialchars($item['estado']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <!-- Descripción -->
                            <div>
                                <p class="text-sm font-semibold text-slate-700 mb-2">Descripción:</p>
                                <p class="text-slate-700 bg-slate-50 rounded-lg p-4"><?= htmlspecialchars($item['descripcion']); ?></p>
                            </div>

                            <!-- Adjunto -->
                            <?php if (!empty($item['adjunto'])): ?>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700 mb-2">Archivo adjunto:</p>
                                    <a href="../adjuntos/<?= htmlspecialchars($item['adjunto']); ?>" target="_blank"
                                       class="inline-flex items-center gap-2 rounded-lg bg-blue-50 text-blue-600 px-4 py-2 text-sm font-semibold hover:bg-blue-100">
                                        <i class="bi bi-file-earmark"></i> Ver archivo
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Respuesta -->
                            <?php if (!empty($item['respuesta'])): ?>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700 mb-2">Respuesta:</p>
                                    <p class="text-slate-700 bg-green-50 rounded-lg p-4 border border-green-200"><?= htmlspecialchars($item['respuesta']); ?></p>
                                </div>
                            <?php else: ?>
                                <!-- Formulario responder -->
                                <form method="POST" class="space-y-3 bg-amber-50 rounded-lg p-4 border border-amber-200">
                                    <input type="hidden" name="id_pqrs" value="<?= $item['id_pqrs']; ?>">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tu respuesta:</label>
                                        <textarea name="respuesta" rows="3" required
                                                  class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                                  placeholder="Escribe la respuesta a esta PQRS..."></textarea>
                                    </div>
                                    <button type="submit" class="rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-500">
                                        <i class="bi bi-send me-2"></i> Enviar Respuesta
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>
</html>
