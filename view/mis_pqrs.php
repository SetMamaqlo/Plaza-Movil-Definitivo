<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once '../model/pqrs_model.php';

if (!isset($_SESSION['user_id_usuario'])) {
    header('Location: login.php');
    exit();
}

$pqrs = PQRSModel::obtenerPorUsuario($_SESSION['user_id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis PQRS - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-slate-50 text-slate-900">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Mis PQRS</h1>
        
        <?php if (empty($pqrs)): ?>
            <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8 text-center">
                <i class="bi bi-inbox text-6xl text-slate-300 mb-4 block"></i>
                <p class="text-lg text-slate-600">No has registrado PQRS aún.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($pqrs as $item): ?>
                    <div class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 p-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($item['asunto']) ?></h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <i class="bi bi-calendar me-1"></i> <?= htmlspecialchars($item['fecha']) ?>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($item['tipo']) ?>
                                </span>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800">
                                    <?= htmlspecialchars($item['estado']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4 mb-4">
                            <p class="text-slate-700"><strong>Descripción:</strong></p>
                            <p class="text-slate-600 mt-2"><?= htmlspecialchars($item['descripcion'] ?? 'N/A') ?></p>
                        </div>

                        <?php if (!empty($item['respuesta'])): ?>
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200 mb-4">
                                <p class="text-sm font-semibold text-green-800 mb-2">
                                    <i class="bi bi-check-circle me-1"></i> Respuesta Recibida
                                </p>
                                <p class="text-slate-700"><?= htmlspecialchars($item['respuesta']) ?></p>
                            </div>
                        <?php else: ?>
                            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200 mb-4">
                                <p class="text-sm font-semibold text-yellow-800">
                                    <i class="bi bi-hourglass-split me-1"></i> Pendiente de respuesta
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['adjunto'])): ?>
                            <div class="flex items-center gap-2 text-sm">
                                <i class="bi bi-file-earmark"></i>
                                <a href="../adjuntos/<?= htmlspecialchars($item['adjunto']) ?>" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-semibold">
                                    Ver archivo adjunto
                                </a>
                            </div>
                        <?php endif; ?>
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