<?php
require_once '../config/database.php';
require_once __DIR__ . '/../fpdf186/fpdf.php';

if (!isset($_GET['id_pago'])) {
    die('No se especificó el pago.');
}

$id_pago = $_GET['id_pago'];

// Datos del pago + pedido + cliente
$stmt = $pdo->prepare("
    SELECT p.*, u.nombre_completo AS cliente, pe.id_pedido, pe.fecha
    FROM pagos p
    JOIN pedidos pe ON pe.id_pedido = p.id_pedido
    JOIN usuarios u ON u.id_usuario = pe.id_usuario
    WHERE p.id_pago = ?
");
$stmt->execute([$id_pago]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    die('Pago no encontrado.');
}

// Productos del pedido con productor
$detallesStmt = $pdo->prepare("
    SELECT pd.cantidad,
           pd.precio_unitario,
           prod.nombre AS producto,
           prod.descripcion,
           COALESCE(usr.nombre_completo, 'Productor') AS productor
    FROM pedido_detalle pd
    JOIN productos prod ON prod.id_producto = pd.id_producto
    LEFT JOIN agricultor ag ON ag.id_agricultor = prod.id_agricultor
    LEFT JOIN usuarios usr ON usr.id_usuario = ag.id_usuario
    WHERE pd.id_pedido = ?
");
$detallesStmt->execute([$pago['id_pedido']]);
$items = $detallesStmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF();
$pdf->AddPage();

// Colores
$primary = [28, 135, 84];
$muted = [90, 112, 127];

// Encabezado
$logo_path = '../img/logoplaza_movil.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 12, 10, 38);
}
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor($primary[0], $primary[1], $primary[2]);
$pdf->Cell(0, 12, utf8_decode('Factura de compra'), 0, 1, 'R');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
$pdf->Cell(0, 6, utf8_decode('Plaza Móvil · Mercado agro'), 0, 1, 'R');
$pdf->Cell(0, 6, utf8_decode('Fecha de pedido: ') . ($pago['fecha'] ?? '—'), 0, 1, 'R');
$pdf->Ln(6);

// Datos principales
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor($primary[0], $primary[1], $primary[2]);
$pdf->SetLineWidth(0.4);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, utf8_decode('Factura #:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, $pago['id_pago'], 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, 'Cliente:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(120, 8, utf8_decode($pago['cliente']), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, utf8_decode('Método de pago:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, ucfirst(utf8_decode($pago['metodo'])), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, 'Estado:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, ucfirst($pago['estado']), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, 'Total:', 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, '$' . number_format($pago['monto'], 2, ',', '.'), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, utf8_decode('Transacción:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, $pago['transaccion_id'], 0, 1, 'L');

$pdf->Ln(10);

// Tabla de ítems
$pdf->SetFillColor($primary[0], $primary[1], $primary[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 9, 'Producto', 0, 0, 'L', true);
$pdf->Cell(35, 9, 'Productor', 0, 0, 'L', true);
$pdf->Cell(20, 9, 'Cant.', 0, 0, 'C', true);
$pdf->Cell(35, 9, 'Unitario', 0, 0, 'R', true);
$pdf->Cell(40, 9, 'Subtotal', 0, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

foreach ($items as $item) {
    $subtotal = $item['cantidad'] * $item['precio_unitario'];
    $pdf->Cell(60, 8, utf8_decode($item['producto']), 'B', 0, 'L');
    $pdf->Cell(35, 8, utf8_decode($item['productor']), 'B', 0, 'L');
    $pdf->Cell(20, 8, $item['cantidad'], 'B', 0, 'C');
    $pdf->Cell(35, 8, '$' . number_format($item['precio_unitario'], 0, ',', '.'), 'B', 0, 'R');
    $pdf->Cell(40, 8, '$' . number_format($subtotal, 0, ',', '.'), 'B', 1, 'R');

    if (!empty($item['descripcion'])) {
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(190, 7, utf8_decode('Descripción: ' . $item['descripcion']), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
    }
}

$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Total a pagar', 0, 0, 'R');
$pdf->SetTextColor($primary[0], $primary[1], $primary[2]);
$pdf->Cell(40, 10, '$' . number_format($pago['monto'], 2, ',', '.'), 0, 1, 'R');
$pdf->SetTextColor(0, 0, 0);

$pdf->Ln(8);
$pdf->SetFont('Arial', 'I', 11);
$pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
$pdf->MultiCell(0, 7, utf8_decode("Gracias por tu compra. Si tienes dudas, escríbenos a soporte@plazamovil.com\nFactura generada automáticamente."), 0, 'C');

$pdf->Output('I', "factura_pago_{$pago['id_pago']}.pdf");
exit;
?>
