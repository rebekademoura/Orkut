<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'RedeSocial';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        } catch (Exception $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>
