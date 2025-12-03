<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 to-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Cambiar Contraseña</h1>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                    <i class="bi bi-check-circle me-2"></i> Contraseña cambiada correctamente.
                </div>
            <?php endif; ?>

            <form action="../controller/resetpasswordcontroller.php" method="POST" class="space-y-4">
                <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    Cambiar Contraseña
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600">
                <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-semibold">Volver al login</a>
            </div>
        </div>
    </div>
</body>
</html>
