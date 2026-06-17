<?php
echo "<h2>Pruebas del Cliente SOAP</h2>";

$options = array(
    'location' => 'http://localhost/proyecto_soap/server.php',
    'uri' => 'http://localhost/proyecto_soap/server.php'
);

try {
    // Instanciar el cliente SOAP
    $cliente = new SoapClient(null, $options);

    // ---------------------------------------------------------
    // PRUEBA 1: Registrar un Producto
    // ---------------------------------------------------------
    echo "<h3>1. Prueba: registrarProducto()</h3>";
    $respuesta_registro = $cliente->registrarProducto("Monitor Gamer", "Monitor de 24 pulgadas 144hz", 150.50, 15, "ACTIVO");
    echo "<b>Respuesta del servidor:</b> " . $respuesta_registro . "<br>";

    // ---------------------------------------------------------
    // PRUEBA 2: Obtener Producto por ID (Buscamos el ID 1)
    // ---------------------------------------------------------
    echo "<h3>2. Prueba: obtenerProductoPorId(1)</h3>";
    $producto = $cliente->obtenerProductoPorId(1);
    echo "<pre>";
    print_r($producto);
    echo "</pre>";

    // ---------------------------------------------------------
    // PRUEBA 3: Listar Productos
    // ---------------------------------------------------------
    echo "<h3>3. Prueba: listarProductos()</h3>";
    $lista = $cliente->listarProductos();
    echo "<pre>";
    print_r($lista);
    echo "</pre>";

} catch (SoapFault $e) {
    echo "<h3 style='color:red;'>Error SOAP detectado:</h3>";
    echo "<b>" . $e->faultcode . ":</b> " . $e->faultstring;
}
?>