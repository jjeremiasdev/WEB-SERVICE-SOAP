<?php
// Le decimos al navegador que responderemos con JSON
header('Content-Type: application/json');

// Recibir los datos enviados por fetch() desde JavaScript
$datos_recibidos = json_decode(file_get_contents('php://input'), true);
$accion = $datos_recibidos['accion'] ?? '';

// Conectar al servidor SOAP
$options = array('uri' => 'http://localhost/proyecto_soap/server.php', 'location' => 'http://localhost/proyecto_soap/server.php');

try {
    $cliente = new SoapClient(null, $options);

    // Identificar qué quiere hacer el usuario
    if ($accion === 'registrar') {
        $respuesta = $cliente->registrarProducto(
            $datos_recibidos['nombre'], 
            $datos_recibidos['descripcion'], 
            $datos_recibidos['precio'], 
            $datos_recibidos['stock'], 
            $datos_recibidos['estado']
        );
        echo json_encode(['status' => 'success', 'mensaje' => $respuesta]);
    } 
    elseif ($accion === 'buscar_id') {
        $producto = $cliente->obtenerProductoPorId($datos_recibidos['id']);
        echo json_encode(['status' => 'success', 'datos' => $producto]);
    }
    elseif ($accion === 'buscar_nombre') {
        $productos = $cliente->obtenerProductoPorNombre($datos_recibidos['nombre']);
        echo json_encode(['status' => 'success', 'datos' => $productos]);
    }
    else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Acción no válida.']);
    }

} catch (SoapFault $e) {
    // Si hay error (ej. precio negativo), devolvemos el error al navegador
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>