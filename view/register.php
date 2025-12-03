<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-gradient-to-br from-emerald-50 to-slate-100 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-slate-100 p-8">
            <div class="text-center mb-8">
                <img src="../img/logoplazamovil.png" alt="Logo Plaza Móvil" class="h-16 w-auto mx-auto mb-4">
                <h1 class="text-3xl font-bold text-slate-900">Crear Cuenta</h1>
            </div>

            <!-- Mostrar alertas de éxito o error -->
            <?php if (isset($_GET['success'])): ?>
                <script>
                    alert('¡Usuario registrado con éxito!');
                    window.location.href = 'login.php';
                </script>
            <?php elseif (isset($_GET['error'])): ?>
                <script>
                    alert('Error al registrar el usuario. Intente nuevamente.');
                </script>
            <?php endif; ?>

            <form action="../controller/registercontroller.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre Completo</label>
                        <input type="text" name="nombre_completo" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo Documento</label>
                        <select name="tipo_documento" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                            <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                            <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Número Documento</label>
                        <input type="text" name="numero_documento" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Teléfono</label>
                        <input type="text" name="telefono" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario</label>
                        <input type="text" name="username" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Contraseña</label>
                        <input type="password" name="password" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Confirmar Contraseña</label>
                        <input type="password" name="confirmar_contrasena" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Usuario</label>
                        <select name="id_rol" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                            <option value="2">Comprador</option>
                            <option value="3">Agricultor</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-200 mt-6">
                    <i class="bi bi-person-plus me-2"></i> Crear Cuenta
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600">
                ¿Ya tienes cuenta? <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-semibold">Inicia sesión aquí</a>
            </div>
        </div>
    </div>
</body>

</html>