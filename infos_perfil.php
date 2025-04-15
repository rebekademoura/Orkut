<?php

include 'conn_bd.php';

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login_cadastro.php'); // Redireciona para a página de login
    exit;
}
class perfil{
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    //informações sobre o meu perfil
    public function getPerfil_total() {
        // Certifique-se de que a sessão esteja ativa
        if (!isset($_SESSION['id_usuario'])) {
            echo "<div class='alert alert-danger'>Usuário não autenticado!</div>";
            return;
        }

        // Prepara a consulta para evitar SQL Injection
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['id_usuario']);
        $stmt->execute();
        $result = $stmt->get_result();

        $dir = "files";
        // Verifica se o usuário foi encontrado
        if ($row = $result->fetch_assoc()) {
            echo "
        <div class='card align-items-center ' style='width: 18rem; background-color: #7fb3d5	 '>
        
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
        
        
            <img src='" . $dir . "/" . $row['id'] . ".png' alt='Imagem' style='width: 200px; height: 200px;'><br>
            <div class='card-body'>
                <h5 class='card-title'>". htmlspecialchars($row['nome_usuario']) ."</h5>
                <p class='card-text'>Gênero: ". htmlspecialchars($row['genero']) .", ". htmlspecialchars($row['cidade']) .", ". htmlspecialchars($row['estado']) ."</p>
                <hr>
                <a href='#' class=''><i class='fi fi-rr-user-add'></i> Adicionar amigos</a>
                <hr>
                <a href='#' class=''><i class='fi fi-rr-add'></i> Postar</a>
                <hr>
                <a href='#' class=''><i class='fi fi-rr-add'></i> Sair da conta</a>

            </div>
        </div>";
        } else {
            echo "<div class='alert alert-warning'>Usuário não encontrado.</div>";
        }

        $stmt->close();
    }


    //informações sobre outros usuários
    public function getPerfil_amigos() {
        if (!isset($_SESSION['id_usuario'])) {
            echo "<script> alert('Usuário não autenticado!'); </script>";
            return;
        }

        $id_usuario_logado = $_SESSION['id_usuario'];

        // Consulta para listar todos os usuários, exceto o logado
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE id != ?");
        $stmt->bind_param('i', $id_usuario_logado);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<div class='row' >";

        $dir = 'files';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Processa o envio do formulário para cada iteração, se aplicável
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_convite']) && $_POST['id_usuario_amigo'] == $row['id']) {
                    $id_usuario_logado = $_SESSION['id_usuario'];
                    $id_usuario_amigo = $row['id'];

                    // Chama o metodo para enviar o convite
                    $mensagem = $this->enviarConviteAmizade($id_usuario_logado, $id_usuario_amigo);
                    echo "<div class='alert alert-info'>$mensagem</div>";
                }

                // Renderiza o formulário
                echo "<div class='col-md-4 align-items-center text-center' >
                <img src='" . $dir . "/" . $row['id'] . ".png' alt='Imagem' style='width: 80px; height: 80px;'><br>
                <a href='' style='font-size: 0.8em;'>" . htmlspecialchars($row['nome_usuario']) . "</a>
                <form method='POST' action=''>
                    <input type='hidden' name='id_usuario_amigo' value='" . $row['id'] . "'>
                    <button type='submit' name='enviar_convite' class='btn btn-success'>Enviar convite</button>
                </form>
              </div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Nenhum amigo encontrado.</div>";
        }


        echo "</div>";
        $stmt->close();
    }


    //enviar convite de amizade
    public function enviarConviteAmizade($id_usuario_logado, $id_usuario_amigo) {
        if (!isset($id_usuario_logado) || !isset($id_usuario_amigo)) {
            return "<div class='alert alert-danger'>Informações inválidas para enviar o convite.</div>";
        }

        // Verifica se já existe uma amizade confirmada ou uma solicitação pendente
        $stmt = $this->conn->prepare("
        SELECT * FROM amizade 
        WHERE 
            (id_usuario_convidou = ? AND id_usuario_aceitou = ?) 
            OR (id_usuario_convidou = ? AND id_usuario_aceitou = ?) 
            AND status IN (1, 2)
    ");
        $stmt->bind_param('iiii', $id_usuario_logado, $id_usuario_amigo, $id_usuario_amigo, $id_usuario_logado);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] == 1) {
                return "<div class='alert alert-warning'>Convite já enviado e está pendente!</div>";
            } elseif ($row['status'] == 2) {
                return "<div class='alert alert-warning'>Vocês já são amigos!</div>";
            }
        }

        // Insere a nova amizade no banco de dados
        $stmt = $this->conn->prepare("INSERT INTO amizade (id_usuario_convidou, id_usuario_aceitou, status) VALUES (?, ?, ?)");
        $status = 1; // Status inicial (aguardando aprovação)
        $stmt->bind_param('iii', $id_usuario_logado, $id_usuario_amigo, $status);

        if ($stmt->execute()) {
            return "<div class='alert alert-success'>Convite enviado com sucesso!</div>";
        } else {
            return "<div class='alert alert-danger'>Erro ao enviar convite: " . $stmt->error . "</div>";
        }
    }


    // Exibir as solicitações de amizade
    public function getSolicitacoesAmizade($id_usuario_logado) {
        $stmt = $this->conn->prepare("SELECT amizade.id, usuario.nome_usuario, amizade.id_usuario_convidou 
                                      FROM amizade
                                      JOIN usuario ON usuario.id = amizade.id_usuario_convidou
                                      WHERE amizade.id_usuario_aceitou = ? AND amizade.status = 1");
        $stmt->bind_param("i", $id_usuario_logado);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='alert alert-info'>
                        <strong>" . htmlspecialchars($row['nome_usuario']) . "</strong> enviou um convite de amizade.
                        <form method='POST'>
                            <input type='hidden' name='id_solicitacao' value='" . $row['id'] . "'>
                            <button type='submit' name='aceitar' class='btn btn-success'>Aceitar</button>
                            <button type='submit' name='recusar' class='btn btn-danger'>Recusar</button>
                        </form>
                      </div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Não há solicitações de amizade pendentes.</div>";
        }

        $stmt->close();
    }


    // Aceitar ou recusar a solicitação de amizade
    public function responderSolicitacaoAmizade($id_solicitacao, $acao) {
        $status = ($acao == 'aceitar') ? 2 : 3;  // 2: aceito, 3: recusado
        $stmt = $this->conn->prepare("UPDATE amizade SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id_solicitacao);
        if ($stmt->execute()) {
            return ($acao == 'aceitar') ? "Solicitação aceita!" : "Solicitação recusada!";
        } else {
            return "Erro ao responder solicitação.";
        }
    }


    // Exibir os amigos do usuário (status 2)
    public function getAmigos($id_usuario_logado) {
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

}

