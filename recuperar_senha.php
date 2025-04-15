<?php
require 'conn_bd.php';

class UsuarioSenha {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function verificarEmail($email) {
        $stmt = $this->conn->prepare("SELECT id FROM usuario WHERE email = ?"); //select no banco de dados
        $stmt->bind_param('s', $email); //envia os parametros
        $stmt->execute(); //executa consulta
        $result = $stmt->get_result(); // resultado
        return $result->num_rows > 0; //retorna o resultado da consulta
    }

    public function alterarSenha($email, $novaSenha) {
        $novaSenhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE usuario SET senha = ? WHERE email = ?");
        $stmt->bind_param('ss', $novaSenhaHash, $email);
        return $stmt->execute();
    }
}

$db = (new Database())->connect();
$usuario = new UsuarioSenha($db);

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    if ($novaSenha !== $confirmarSenha) {
        $message = "<div class='alert alert-danger'>As senhas não coincidem.</div>";
    } elseif ($usuario->verificarEmail($email)) {
        if ($usuario->alterarSenha($email, $novaSenha)) {
            $message = "<div class='alert alert-success'>Senha alterada com sucesso! <a href='login_cadastro.php'>Faça login</a>.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Erro ao alterar a senha. Tente novamente.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Email não encontrado.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #aed6f1">
<div class="container mt-5" >
    <h2 class="text-center">Orkute - Recuperação de Senha</h2>
    <?= $message ?>
    <form method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="nova_senha" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
        </div>
        <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
        </div>
        <button type="submit" class="btn btn-primary">Alterar Senha</button>
        <a href="login_cadastro.php" class="btn btn-link">Cancelar</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
