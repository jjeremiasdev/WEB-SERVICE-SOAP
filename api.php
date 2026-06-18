<?php
header('Content-Type: application/json');
$datos_recibidos = json_decode(file_get_contents('php://input'), true);
$accion = $datos_recibidos['accion'] ?? '';

// Aseguramos que el 'uri' coincida con el del servidor
$options = array(
    'uri' => 'http://proyecto_soap/seguridad', 
    'location' => 'http://localhost/proyecto_soap/server.php'
);

try {
    $cliente = new SoapClient(null, $options);

    // ========================================================
    // CREACIÓN DEL HEADER WS-SECURITY (UsernameToken)
    // ========================================================
    $auth = new stdClass();
    $auth->usuario = "admin";          // Usuario correcto
    $auth->password = "admin";   // Clave correcta

    // Creamos la cabecera SOAP llamando a la función 'Autenticacion' del servidor
    $header_seguridad = new SoapHeader('http://proyecto_soap/seguridad', 'Autenticacion', $auth);
    
    // Inyectamos la cabecera en el cliente antes de hacer peticiones
    $cliente->__setSoapHeaders($header_seguridad);
    // ========================================================

    if ($accion === 'registrar') {
        $respuesta = $cliente->registrarProducto($datos_recibidos['nombre'], $datos_recibidos['descripcion'], $datos_recibidos['precio'], $datos_recibidos['stock'], $datos_recibidos['estado']);
        echo json_encode(['status' => 'success', 'mensaje' => $respuesta]);
    } 
    elseif ($accion === 'buscar_id') {
        $producto = $cliente->obtenerProductoPorId($datos_recibidos['id']);
        echo json_encode(['status' => 'success', 'datos' => $producto]);
    }
    elseif ($accion === 'buscar_nombre') {
        $productos = $cliente->obtenerProductoPorNombre($datos_recibidos['nombre']);
        echo json_encode(['status' => 'success', 'datos' => $productos]);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Acción no válida.']);
    }
} catch (SoapFault $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>