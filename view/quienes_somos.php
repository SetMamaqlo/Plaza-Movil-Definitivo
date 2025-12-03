<?php
require_once __DIR__ . '/../config/app.php';
session_start();
require_once __DIR__ . '/config/session_timeout.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Quiénes Somos? - Plaza Móvil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-slate-50 text-slate-900">
    <!-- Navbar -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Espacio para no tapar contenido -->
    <div style="height:70px"></div>

    <div class="mx-auto max-w-6xl px-6 py-12">
        <!-- Título -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-emerald-700 mb-3">¿Quiénes Somos?</h1>
            <p class="text-lg text-slate-600">Conectamos agricultores y compradores para un comercio justo y directo.
            </p>
        </div>

        <!-- Sección Misión -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-slate-100">
                <img src="../img/mision.jpg" alt="Misión Plaza Móvil" class="w-full h-64 object-cover">
            </div>
            <div class="flex flex-col justify-center">
                <h2 class="text-3xl font-bold text-emerald-700 mb-4">Nuestra Misión</h2>
                <p class="text-slate-700 leading-relaxed">
                    En <strong>Plaza Móvil</strong>, nuestra misión es crear un puente directo entre agricultores locales y
                    compradores, promoviendo el comercio justo, el consumo de productos frescos y el desarrollo de
                    comunidades rurales. Eliminamos intermediarios, empoderamos a los productores y ofrecemos precios
                    accesibles a los clientes.
                </p>
            </div>
        </div>

        <!-- Sección Visión -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="flex flex-col justify-center order-2 lg:order-1">
                <h2 class="text-3xl font-bold text-emerald-700 mb-4">Nuestra Visión</h2>
                <p class="text-slate-700 leading-relaxed">
                    Aspiramos a ser la plataforma líder en Latinoamérica en la digitalización de mercados agrícolas,
                    facilitando el acceso a productos frescos y saludables mientras apoyamos a los pequeños y medianos
                    agricultores. Queremos construir un ecosistema donde tecnología, sostenibilidad y comercio justo
                    trabajen de la mano.
                </p>
            </div>
            <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-slate-100 order-1 lg:order-2">
                <img src="../img/vision.jpg" alt="Visión Plaza Móvil" class="w-full h-64 object-cover">
            </div>
        </div>

        <!-- Sección Problemática -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-slate-100">
                <img src="../img/problematica.jpg" alt="Problemática" class="w-full h-64 object-cover">
            </div>
            <div class="flex flex-col justify-center">
                <h2 class="text-3xl font-bold text-red-600 mb-4">La Problemática</h2>
                <p class="text-slate-700 leading-relaxed">
                    Los agricultores locales enfrentan grandes dificultades: bajos precios de venta por intermediación,
                    falta de canales de distribución directa y desconocimiento de herramientas digitales para
                    promocionar sus productos. Al mismo tiempo, los consumidores pagan altos costos y tienen poco
                    acceso a alimentos frescos y locales.
                </p>
            </div>
        </div>

        <!-- Sección Soluciones -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="flex flex-col justify-center order-2 lg:order-1">
                <h2 class="text-3xl font-bold text-amber-600 mb-4">Nuestras Soluciones</h2>
                <ul class="space-y-3">
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold text-xl">✓</span> <span><strong>Plataforma en línea:</strong> Conecta agricultores y compradores de manera fácil y rápida.</span>
                    </li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold text-xl">✓</span> <span><strong>Precios justos:</strong> Eliminamos intermediarios para garantizar ganancias justas y productos más
                            económicos.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold text-xl">✓</span> <span><strong>Promoción digital:</strong> Ayudamos a los agricultores a exhibir sus productos de forma
                            profesional.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold text-xl">✓</span> <span><strong>Acceso a productos frescos:</strong> Ofrecemos alimentos locales, frescos y de alta
                            calidad.</span></li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold text-xl">✓</span> <span><strong>Comunidad sostenible:</strong> Fomentamos el consumo responsable y el apoyo a
                            productores locales.</span></li>
                </ul>
            </div>
            <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-slate-100 order-1 lg:order-2">
                <img src="../img/soluciones.jpg" alt="Soluciones" class="w-full h-64 object-cover">
            </div>
        </div>
    </div>

    <footer class="mt-14 bg-white py-6 text-center text-sm text-slate-500 shadow-inner">
        &copy; 2025 Plaza Móvil. Todos los derechos reservados.
    </footer>
</body>

</html>