<?php

require_once 'conn_bd.php';

class Busca
{
    private $conexao;
    private $nome;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    // Metodo para buscar usuários e comunidades
    public function buscar($nome)
    {
        $this->nome = $nome;

        $stmt = $this->conn->prepare("SELECT usuario WHERE nome = %".$nome."%");
        $stmt->bind_param("i", $nome);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='alert alert-info'>
                        <strong>" . htmlspecialchars($row['nome_usuario']) . "</strong>
                      </div>";
            }
        } else {

        }

        $stmt->close();




    }



    /*
     *     public function getBusca($id_usuario_logado) {
        // Prepare a consulta SQL com UNION DISTINCT para evitar duplicações
        $stmt = $this->conn->prepare("SELECT usuario.id, usuario.nome_usuario
                                  FROM amizade
                                  JOIN usuario ON usuario.id = amizade.id_usuario_convidou
                                  WHERE amizade.id_usuario_aceitou = ? AND amizade.status = 2
                                  UNION DISTINCT
                                  SELECT usuario.id, usuario.nome_usuario
                                  FROM amizade
                                  JOIN usuario ON usuario.id = amizade.id_usuario_aceitou
                                  WHERE amizade.id_usuario_convidou = ? AND amizade.status = 2");

        // Verifique se a preparação da consulta falhou
        if (!$stmt) {
            die("Erro na preparação da consulta: " . $this->conn->error);
        }

        // Bind os parâmetros
        $stmt->bind_param("ii", $id_usuario_logado, $id_usuario_logado);

        // Execute a consulta
        $stmt->execute();

        // Verifique se a execução falhou
        if ($stmt->error) {
            die("Erro ao executar a consulta: " . $stmt->error);
        }

        // Obtenha o resultado
        $result = $stmt->get_result();
        $dir = 'files';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='alert alert-success'>

                    <img src='" . $dir . "/" . $row['id'] . ".png' alt='Imagem' style='width: 80px; height: 80px;'>
                    <strong>" . htmlspecialchars($row['nome_usuario']) . "</strong> é seu amigo.
                  </div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Você ainda não tem amigos.</div>";
        }

        $stmt->close();
    }

     *
     * */


}



