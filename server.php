<?php
class ProductoService {
    private $db;
    private $autenticado = false; // Variable de seguridad

    public function __construct() {
        $host = '127.0.0.1';
        $db_name = 'empresa_db';
        $user = 'root';
        $pass = ''; 

        try {
            $this->db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Error de conexión: " . $e->getMessage());
        }
    }

    // 1. Método que intercepta el Header de WS-Security
    public function Autenticacion($credenciales) {
        // Para este proyecto académico, definimos un usuario y clave maestro
        $usuario_valido = "admin";
        $clave_valida = "admin";

        if (isset($credenciales->usuario) && $credenciales->usuario === $usuario_valido &&
            isset($credenciales->password) && $credenciales->password === $clave_valida) {
            $this->autenticado = true;
        } else {
            throw new SoapFault("Client", "WS-Security: Credenciales incorrectas. Acceso denegado.");
        }
    }

    // 2. Método barrera
    private function verificarSeguridad() {
        if (!$this->autenticado) {
            throw new SoapFault("Client", "WS-Security: Petición denegada. Cabecera de seguridad ausente.");
        }
    }

    // 3. Aplicar la barrera a todas las operaciones
    public function listarProductos() {
        $this->verificarSeguridad(); // <-- Barrera
        $stmt = $this->db->query("SELECT * FROM productos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductoPorId($id) {
        $this->verificarSeguridad(); // <-- Barrera
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) return $producto;
        throw new SoapFault("Client", "Error: Producto no encontrado");
    }

    public function obtenerProductoPorNombre($nombre) {
        $this->verificarSeguridad(); // <-- Barrera
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE nombre LIKE :nombre");
        $termino = "%" . $nombre . "%";
        $stmt->bindParam(':nombre', $termino, PDO::PARAM_STR);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($productos) return $productos;
        throw new SoapFault("Client", "No se encontraron productos con ese nombre.");
    }

    public function registrarProducto($nombre, $descripcion, $precio, $stock, $estado) {
        $this->verificarSeguridad(); // <-- Barrera
        
        if (empty(trim($nombre))) throw new SoapFault("Client", "El nombre no puede estar vacío.");
        if ($precio <= 0) throw new SoapFault("Client", "El precio debe ser mayor a 0.");
        if ($stock < 0) throw new SoapFault("Client", "El stock debe ser mayor o igual a 0.");
        if ($estado !== "ACTIVO" && $estado !== "INACTIVO") throw new SoapFault("Client", "El estado solo puede ser ACTIVO o INACTIVO.");

        try {
            $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, estado) VALUES (:nombre, :descripcion, :precio, :stock, :estado)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':nombre'=>$nombre, ':descripcion'=>$descripcion, ':precio'=>$precio, ':stock'=>$stock, ':estado'=>$estado]);
            return "Producto registrado exitosamente.";
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Error interno: " . $e->getMessage());
        }
    }
}

// El namespace debe coincidir exactamente con el que usemos en el cliente
$options = array('uri' => 'http://proyecto_soap/seguridad');
$server = new SoapServer(null, $options);
$server->setClass('ProductoService');
$server->handle();
?>