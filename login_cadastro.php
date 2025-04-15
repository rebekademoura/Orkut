<?php
require 'conn_bd.php';
session_start();

class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    function add_imagem($imagem){
        // Definir o diretório de destino
        $target_dir = "files/";

        // Verificar se a pasta existe, caso contrário, criar
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Cria a pasta com permissões 0777
        }

        $fileExtension = strtolower(pathinfo($imagem["name"], PATHINFO_EXTENSION)); // Extrair a extensão do arquivo

        $novo_nome_arquivo = $target_dir . $_SESSION['id_usuario'] . '.' . $fileExtension;// Definir o nome do arquivo como o ID do usuário

        $uploadOk = true; //ver se o upload deu certo

        // Verificar se o arquivo é uma imagem
        $check = getimagesize($imagem["tmp_name"]);
        if ($check !== false) {
            echo "O arquivo é uma imagem - " . $check["mime"] . ".<br>";
        } else {
            echo "O arquivo não é uma imagem.<br>";
            $uploadOk = false;
        }

        // Verificar se o arquivo já existe
        if (file_exists($novo_nome_arquivo)) {
            echo "A imagem já existe.<br>";
            $uploadOk = false;
        }

        // Verificar o tamanho do arquivo
        if ($imagem["size"] > 500000) {  // Limite de 500KB
            echo "O arquivo é muito grande.<br>";
            $uploadOk = false;
        }

        // Permitir certos formatos de arquivo
        if ( $fileExtension != "png") {
            echo "Somente arquivos JPG, JPEG e PNG são aceitos.<br>";
            $uploadOk = false;
        }

        // Verificar se $uploadOk está definido como falso
        if ($uploadOk == false) {
            echo "A imagem não foi enviada.<br>";
        } else {
            // Tenta mover o arquivo para o diretório de destino
            if (move_uploaded_file($imagem["tmp_name"], $novo_nome_arquivo)) {
                echo "O arquivo " . htmlspecialchars(basename($imagem["name"])) . " foi salvo com o nome: $novo_nome_arquivo.<br>";
            } else {
                echo "Houve um erro ao salvar o arquivo.<br>";
            }
        }
    }

    public function cadastrar($nome, $email, $senha, $genero, $cidade, $estado, $imagem) {
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO usuario (nome_usuario, email, senha, genero, cidade, estado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $nome, $email, $senhaHash, $genero, $cidade, $estado);

        if ($stmt->execute()) {
            $_SESSION['id_usuario'] = $this->conn->insert_id; // Define o ID do usuário após o cadastro
            $_SESSION['nome'] = $nome;
            $_SESSION['email'] = $email;
            $_SESSION['genero'] = $genero;
            $_SESSION['cidade'] = $cidade;
            $_SESSION['estado'] = $estado;

            // Agora, chamamos a função para salvar a imagem, passando o arquivo diretamente
            $this->add_imagem($imagem);

            return true; // Retorna true para indicar que o cadastro foi bem-sucedido
        }

        return false; // Retorna false se houve erro
    }

    public function logar($email, $senha) {
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['id_usuario'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome_usuario'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['genero'] = $usuario['genero'];
                $_SESSION['cidade'] = $usuario['cidade'];
                $_SESSION['estado'] = $usuario['estado'];

                header('location: inicial.php');
            }
        }
        return false; // Login falhou
    }
}

$db = (new Database())->connect();
$usuario = new Usuario($db);

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Cadastro
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $genero = $_POST['genero'];
        $cidade = $_POST['cidade'];
        $estado = $_POST['estado'];
        $imagem = $_FILES['imagem'];

        // Tenta cadastrar o usuário
        if ($usuario->cadastrar($nome, $email, $senha, $genero, $cidade, $estado, $imagem)) {
            header('Location: inicial.php'); // Redireciona para a página inicial após cadastro
            exit;
        }




    } elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $userId = $usuario->logar($email, $senha);
        if ($userId) {
            $message = "<div class='alert alert-success'>Login bem-sucedido! ID do usuário: $userId</div>";
        } else {
            $message = "<div class='alert alert-danger'>Email ou senha incorretos.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro e Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #F0FFFF		">
<div class="container">
    <div class="row mt-5" >
        <?= $message ?> <!-- Exibe as mensagens de sucesso ou erro -->
        <div class="col-12 text-center">
            <h1>Ortkute</h1>
        </div>
        <div class="col-6 card" style="background-color: #87CEFA">
            <h2>Cadastro de Usuário</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>
                <div class="mb-3">
                    <label for="genero" class="form-label">Gênero</label>
                    <input type="text" class="form-control" id="genero" name="genero" required>
                </div>
                <div class="mb-3">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" required>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="estado" name="estado" required>
                </div>
                <div class="mb-3">
                    <label for="imagem" class="form-label">Imagem (opcional)</label>
                    <input type="file" class="form-control" id="imagem" name="imagem">
                </div>
                <button type="submit" class="btn btn-primary" name="register">Cadastrar</button>
            </form>
        </div>

        <div class="col-6 card" style="background-color: #87CEFA">
            <h2>Login</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="emailLogin" class="form-label">Email</label>
                    <input type="email" class="form-control" id="emailLogin" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="senhaLogin" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senhaLogin" name="senha" required>
                </div>
                <button type="submit" class="btn btn-primary" name="login">Entrar</button>
            </form>
            <a href="recuperar_senha.php">recuperar senha</a>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
