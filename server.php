<?php
class ProductoService {
    private $db;

    public function __construct() {
        // Credenciales por defecto de XAMPP
        $host = '127.0.0.1';
        $db_name = 'empresa_db';
        $user = 'root';
        $pass = ''; 

        try {
            // Conexión segura usando PDO
            $this->db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Error de conexión a la BD: " . $e->getMessage());
        }
    }

    public function listarProductos() {
        $stmt = $this->db->query("SELECT * FROM productos");
        // Devuelve un arreglo asociativo con todos los productos
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductoPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            return $producto;
        } else {
            throw new SoapFault("Client", "Error: Producto no encontrado");
        }
    }
    public function obtenerProductoPorNombre($nombre) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE nombre LIKE :nombre");
        $termino = "%" . $nombre . "%"; // Permite buscar coincidencias parciales
        $stmt->bindParam(':nombre', $termino, PDO::PARAM_STR);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($productos) {
            return $productos;
        } else {
            throw new SoapFault("Client", "No se encontraron productos con ese nombre.");
        }
    }

    public function registrarProducto($nombre, $descripcion, $precio, $stock, $estado) {
        // -- Validaciones Mínimas --
        if (empty(trim($nombre))) {
            throw new SoapFault("Client", "Validación fallida: El nombre no puede estar vacío.");
        }
        if ($precio <= 0) {
            throw new SoapFault("Client", "Validación fallida: El precio debe ser mayor a 0.");
        }
        if ($stock < 0) {
            throw new SoapFault("Client", "Validación fallida: El stock debe ser mayor o igual a 0.");
        }
        if ($estado !== "ACTIVO" && $estado !== "INACTIVO") {
            throw new SoapFault("Client", "Validación fallida: El estado solo puede ser ACTIVO o INACTIVO.");
        }

        // -- Inserción en Base de Datos --
        try {
            $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, estado) VALUES (:nombre, :descripcion, :precio, :stock, :estado)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();

            return "Producto registrado exitosamente.";
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Error interno al guardar: " . $e->getMessage());
        }
    }
}

// Configuración del servidor SOAP en modo Non-WSDL
$options = array(
    'uri' => 'http://localhost/proyecto_soap/server.php'
);

$server = new SoapServer(null, $options);
$server->setClass('ProductoService');
$server->handle();
?>