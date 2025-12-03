<?php
require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 to-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Recuperar Contraseña</h1>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                    <i class="bi bi-check-circle me-2"></i> Si el correo existe, se enviarán instrucciones.
                </div>
            <?php endif; ?>

            <form action="<?= base_url() ?>/controller/forgotpasswordcontroller.php" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                           placeholder="tu@email.com">
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    <i class="bi bi-envelope me-2"></i> Enviar Instrucciones
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600">
                <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-semibold">Volver al login</a>
            </div>
        </div>
    </div>
</body>
</html>
