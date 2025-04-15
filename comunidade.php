<?php

require_once 'conn_bd.php';


if (!isset($_SESSION['id_usuario'])) {
    header('Location: login_cadastro.php'); // Redireciona para a página de login
    exit;
}
class Comunidade {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Metodo para receber e validar os dados do formulário
    public function receberDadosFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_comunidade'])) {
            $nome = trim($_POST['nome_comunidade']);
            $descricao = trim($_POST['descricao_comunidade']);
            $id_criador = $_SESSION['id_usuario'];

            if (empty($nome) || empty($descricao)) {
                echo "<script> alert('Preencha todos os campos!'); </script>";
                return null;
            }

            return [
                'nome' => $nome,
                'descricao' => $descricao,
                'id_criador' => $id_criador,
            ];
        }
        return null;
    }


    // Metodo para inserir comunidade no banco de dados
    public function adicionarComunidade($dados) {
        if (!$dados) return;

        $stmt = $this->conn->prepare("INSERT INTO comunidade (nome_comunidade, descricao, id_criador) VALUES (?, ?, ?)");

        if (!$stmt) {
            die("<div class='alert alert-danger'>Erro na preparação da consulta: " . $this->conn->error . "</div>");
        }

        $stmt->bind_param("ssi", $dados['nome'], $dados['descricao'], $dados['id_criador']);

        if ($stmt->execute()) {
            echo "<script> alert('Comunidade adicionada'); </script>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao adicionar comunidade: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }


    // Metodo para mostrar o nome das comunidades
    public function mostrarComunidades() {
        $stmt = $this->conn->prepare("SELECT id_comunidade, nome_comunidade FROM comunidade");


        if (!$stmt) {
            die("<div class='alert alert-danger'>Erro na preparação da consulta: " . $this->conn->error . "</div>");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        echo "<div class='row' >";

        if ($result->num_rows > 0) {
            echo "<ul class='list-group'>";
            while ($row = $result->fetch_assoc()) {
                echo "<div class='col-md-4 align-items-center text-center' >
                <a href='' style='font-size: 0.8em;'>" . htmlspecialchars($row['nome_comunidade']) . "</a>
                <form method='POST' action=''>
                    <input type='hidden' name='id_comunidade' value='" . $row['id_comunidade'] . "'>
                    <button type='submit' name='entrar_comunidade' class='btn btn-success'>Entrar na comunidade</button>
                </form>
              </div>";
            }
            echo "</ul>";
        } else {
            echo "<script> alert('Nenhuma comunidade encontrada!'); </script>";
        }
        echo "</div>";
        $stmt->close();
    }

    public function mostrarComunidadesDetalhes() {
        $stmt = $this->conn->prepare("SELECT id_comunidade, nome_comunidade FROM comunidade");


        if (!$stmt) {
            die("<div class='alert alert-danger'>Erro na preparação da consulta: " . $this->conn->error . "</div>");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        echo "<div class='row' >";

        if ($result->num_rows > 0) {
            echo "<ul class='list-group'>";
            while ($row = $result->fetch_assoc()) {
                echo "<div class='' >
                <a class='ms-5' href='' style='font-size: 0.8em;'>" . htmlspecialchars($row['nome_comunidade']) . "</a>
                w
                <hr>
              </div>";
            }
            echo "</ul>";
        } else {
            echo "<script> alert('Nenhuma comunidade encontrada!'); </script>";
        }
        echo "</div>";
        $stmt->close();
    }


    // Metodo para entrar em uma comunidade
    public function entrarComunidade($id_usuario_logado, $id_comunidade) {
        if (!isset($id_usuario_logado) || !isset($id_comunidade)) {
            return "<script> alert('Sem informações suficientes para entrar em uma comunidade!'); </script>";
        }

        // Verifica se o usuário já participa da comunidade
        $stmt = $this->conn->prepare("
            SELECT * FROM participa_comunidade 
            WHERE id_usuario = ? AND id_comunidade = ?
        ");
        $stmt->bind_param('ii', $id_usuario_logado, $id_comunidade);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return "<script> alert('Você já participa desta comunidade!'); </script>";
        }

        // Insere o registro na tabela participa_comunidade
        $stmt = $this->conn->prepare("
            INSERT INTO participa_comunidade (id_usuario, id_comunidade) 
            VALUES (?, ?)
        ");
        $stmt->bind_param('ii', $id_usuario_logado, $id_comunidade);

        if ($stmt->execute()) {
            return "<script> alert('Você faz parte da comunidade!'); </script>";
        } else {
            return "<script> alert('Erro ao entrar na comunidade!'); </script>";
        }
    }


    // Metodo para mostrar o nome das comunidades
    public function mostrarComunidadesParticipante($id_usuario) {
        $stmt = $this->conn->prepare("
        SELECT c.id_comunidade, c.nome_comunidade 
        FROM comunidade c
        INNER JOIN participa_comunidade pc 
        ON c.id_comunidade = pc.id_comunidade
        WHERE pc.id_usuario = ?
    ");

        if (!$stmt) {
            die("<div class='alert alert-danger'>Erro na preparação da consulta: " . $this->conn->error . "</div>");
        }

        // Vincula o ID do usuário
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<div class='row'>";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "
            <div class='col-md-4 align-items-center text-center'>
                <a href='detalhes_comunidade.php?id=" . $row['id_comunidade'] . "' style='font-size: 0.8em;'>
                    " . htmlspecialchars($row['nome_comunidade']) . "
                </a>
            </div>";
            }
        } else {
            echo "<script>alert('Nenhuma comunidade encontrada!');</script>";
        }

        echo "</div>";
        $stmt->close();
    }
}
