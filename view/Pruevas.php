<?php
session_start();
if (!isset($_SESSION['user_id_usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once __DIR__ . '/../config/session_timeout.php';

$user_id_usuario = $_SESSION['user_id_usuario'];

$stmt = $pdo->prepare("
    SELECT u.*, r.nombre, a.id_agricultor, a.certificaciones, a.fotos, 
           a.metodo_entrega, a.metodos_de_pago, z.zona AS nombre_zona
    FROM usuarios u
    LEFT JOIN rol r ON u.id_rol = r.id_rol
    LEFT JOIN agricultor a ON u.id_usuario = a.id_usuario
    LEFT JOIN zona z ON a.id_zona = z.id_zona
    WHERE u.id_usuario  = ?
");
$stmt->execute([$user_id_usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo '<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">Usuario no encontrado.</div>';
    exit();
}

$fotosTerreno = !empty($user['fotos']) ? explode(',', $user['fotos']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario Avanzado - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gradient-to-b from-emerald-50 to-white min-h-screen">
    <?php include '../navbar.php'; ?>
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-slate-100 p-8">
            <!-- Encabezado del Perfil -->
            <div class="text-center mb-8">
                <img src="<?php echo !empty($user['Foto']) ? '../img/' . htmlspecialchars($user['Foto']) : '../img/default_profile.png'; ?>"
                    alt="Foto de perfil" class="h-32 w-32 rounded-full object-cover mx-auto mb-4 ring-4 ring-emerald-100">
                <h1 class="text-3xl font-bold text-slate-900"><?php echo htmlspecialchars($user['nombre_completo']); ?></h1>
                <p class="text-slate-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                <p class="text-sm text-slate-500">Rol: <?php echo htmlspecialchars($user['nombre']); ?></p>
            </div>

            <!-- Información Personal -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="bi bi-person-lines-fill me-2"></i>Información Personal</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100">
                        <strong class="text-slate-900">Email:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100">
                        <strong class="text-slate-900">Teléfono:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['telefono'] ?? 'No disponible'); ?></p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100 md:col-span-2">
                        <strong class="text-slate-900">Documento:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars(($user['tipo_documento'] ?? 'N/A') . ' ' . ($user['numero_documento'] ?? '')); ?></p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-4 py-3 border border-emerald-100 md:col-span-2">
                        <strong class="text-slate-900">Fecha de Nacimiento:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['fecha_nacimiento'] ?? 'No disponible'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Información de Agricultor (si aplica) -->
            <?php if ($user['id_rol'] == 3): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="bi bi-leaf me-2"></i>Información de Agricultor</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-lg bg-blue-50 px-4 py-3 border border-blue-100">
                        <strong class="text-slate-900">Zona:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['nombre_zona'] ?? 'No asignada'); ?></p>
                    </div>
                    <div class="rounded-lg bg-blue-50 px-4 py-3 border border-blue-100">
                        <strong class="text-slate-900">Certificaciones:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['certificaciones'] ?? 'Ninguna'); ?></p>
                    </div>
                    <div class="rounded-lg bg-blue-50 px-4 py-3 border border-blue-100">
                        <strong class="text-slate-900">Método de Entrega:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['metodo_entrega'] ?? 'No especificado'); ?></p>
                    </div>
                    <div class="rounded-lg bg-blue-50 px-4 py-3 border border-blue-100">
                        <strong class="text-slate-900">Métodos de Pago:</strong>
                        <p class="text-slate-700"><?php echo htmlspecialchars($user['metodos_de_pago'] ?? 'No especificado'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Fotos del Terreno -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-emerald-700 mb-4"><i class="bi bi-images me-2"></i>Fotos del Terreno</h2>
                <?php if (!empty($fotosTerreno)): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php foreach ($fotosTerreno as $foto): 
                            $f = trim($foto);
                            if ($f === '') continue;
                        ?>
                            <div class="rounded-lg overflow-hidden shadow-md ring-1 ring-slate-100 cursor-pointer hover:shadow-lg transition"
                                 onclick="verFoto('../uploads/<?php echo htmlspecialchars($f); ?>')">
                                <img src="../uploads/<?php echo htmlspecialchars($f); ?>" 
                                    class="w-full h-32 object-cover hover:scale-110 transition"
                                    alt="Foto del terreno">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-600 italic"><i class="bi bi-info-circle me-2"></i>No hay fotos registradas del terreno.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Botones de Acción -->
            <div class="mt-8 flex flex-wrap gap-3">
                <button onclick="document.getElementById('modalEditarPerfil').classList.remove('hidden')" 
                        class="rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-3 transition">
                    <i class="bi bi-pencil me-2"></i>Editar Perfil
                </button>
                <a href="historialpedidos.php" class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 transition inline-block">
                    <i class="bi bi-clock-history me-2"></i>Historial de Pedidos
                </a>
                <button onclick="document.getElementById('modalEliminarPerfil').classList.remove('hidden')" 
                        class="rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 transition">
                    <i class="bi bi-trash me-2"></i>Eliminar Perfil
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div id="modalEditarPerfil" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900">Editar Perfil</h3>
                <button onclick="document.getElementById('modalEditarPerfil').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 text-2xl">&times;</button>
            </div>
            <form action="../controller/editarperfilcontroller.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>" required 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                            <input type="email" name="correo" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario</label>
                            <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['username']); ?>" required 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Teléfono</label>
                            <input type="text" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>

                        <?php if ($user['id_rol'] == 3): ?>
                        <hr class="my-4 border-slate-200">
                        <h4 class="font-bold text-slate-900">Datos de Agricultor</h4>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Certificaciones</label>
                            <textarea name="certificaciones" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" rows="2"><?php echo htmlspecialchars($user['certificaciones']); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Método de Entrega</label>
                            <input type="text" name="metodo_entrega" value="<?php echo htmlspecialchars($user['metodo_entrega']); ?>" 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Métodos de Pago</label>
                            <input type="text" name="metodos_de_pago" value="<?php echo htmlspecialchars($user['metodos_de_pago']); ?>" 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nuevas fotos del terreno</label>
                            <input type="file" name="nuevas_fotos[]" accept="image/*" multiple 
                                   class="w-full">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="md:col-span-1 text-center">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Foto de Perfil</label>
                        <img id="previewProfile" src="<?php echo !empty($user['Foto']) ? '../img/' . htmlspecialchars($user['Foto']) : '../img/default_profile.png'; ?>" 
                             alt="preview perfil" class="h-32 w-32 rounded-full object-cover mx-auto mb-2 ring-4 ring-emerald-100">
                        <input type="file" name="foto_perfil" accept="image/*" 
                               class="w-full text-sm">
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('modalEditarPerfil').classList.add('hidden')" 
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Eliminar Perfil -->
    <div id="modalEliminarPerfil" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Eliminar Perfil</h3>
            <p class="text-slate-600 mb-6">¿Estás seguro? Esta acción no se puede deshacer.</p>
            <form action="../controller/eliminarperfilcontroller.php" method="POST">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirma tu contraseña:</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('modalEliminarPerfil').classList.add('hidden')" 
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function verFoto(src) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur';
            modal.onclick = () => modal.remove();
            modal.innerHTML = `<img src="${src}" class="max-w-2xl max-h-screen rounded-lg shadow-2xl">`;
            document.body.appendChild(modal);
        }
    </script>
</body>
</html>
