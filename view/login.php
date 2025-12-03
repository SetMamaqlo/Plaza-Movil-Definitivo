<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-gradient-to-br from-emerald-50 to-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <img src="../img/logoplazamovil.png" alt="Logo Plaza Móvil" class="h-16 w-auto mx-auto mb-4">
                <h1 class="text-3xl font-bold text-slate-900">Iniciar Sesión</h1>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <i class="bi bi-exclamation-circle me-2"></i> Usuario o contraseña incorrectos.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                    <i class="bi bi-check-circle me-2"></i> Usuario eliminado correctamente.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
                <div class="mb-4 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm">
                    <i class="bi bi-exclamation-triangle me-2"></i> Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.
                </div>
            <?php endif; ?>

            <form action="../controller/logincontroller.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">
                
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Usuario o Correo</label>
                    <input type="text" id="username" name="username" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" 
                           placeholder="usuario@example.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100" 
                           placeholder="••••••••">
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                </button>
            </form>

            <div class="mt-6 space-y-3 text-center">
                <a href="forgot_password.php" class="inline-block text-sm text-emerald-600 hover:text-emerald-700 font-semibold">
                    ¿Olvidaste tu contraseña?
                </a>
                <div class="text-sm text-slate-600">
                    ¿No tienes cuenta? <a href="register.php" class="text-emerald-600 hover:text-emerald-700 font-semibold">Regístrate aquí</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>