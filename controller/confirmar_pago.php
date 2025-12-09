<?php
// La integración con Mercado Pago fue eliminada. Todos los pedidos se pagan en efectivo contra entrega.
http_response_code(410);
echo "Pasarela de pago deshabilitada. Usa el flujo de pago en efectivo.";
