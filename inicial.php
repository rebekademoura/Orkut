<?php

include 'infos_perfil.php'; //incluindo classe infos_perfil
require_once 'comunidade.php'; // incluindo classe comunidade
require_once 'busca.php';


//verifica se usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login_cadastro.php');
    exit;
}

// Conexão ao banco de dados
$db = (new Database())->connect();

$perfil = new perfil($db);
$perfil = new perfil($db);
$busca = new Busca($db);

// Inicialização da classe Comunidade
$comunidade = new Comunidade($db);

// Receber os dados do formulário
$dados = $comunidade->receberDadosFormulario();

if ($dados) { //se tiver informações
    $comunidade->adicionarComunidade($dados);
}



// Verificar se foi clicado "Aceitar" ou "Recusar" amigo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aceitar'])) {
        $id_solicitacao = $_POST['id_solicitacao'];
        echo $perfil->responderSolicitacaoAmizade($id_solicitacao, 'aceitar');
    } elseif (isset($_POST['recusar'])) {
        $id_solicitacao = $_POST['id_solicitacao'];
        echo $perfil->responderSolicitacaoAmizade($id_solicitacao, 'recusar');
    }
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<body style="background-color: #aed6f1">

<nav class="navbar bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand">Orkute</a>
        <a class="btn btn-info" href="?page=perfil">Meu Perfil</a> <!--seleciona a página desejada-->
        <a class="btn btn-info" href="?page=posts">Posts</a> <!--seleciona a página desejada-->

        <?php
        // Verifica se o nome foi passado no formulário de busca
        if (isset($_GET['nome']) && !empty($_GET['nome'])) {
            $nome = $_GET['nome'];


            $busca->buscar($nome);

            // Exibe os resultados encontrados
            echo "<h3>Usuários Encontrados:</h3>";
            if (empty($resultado['usuarios'])) {
                echo "<p>Nenhum usuário encontrado.</p>";
            } else {
                foreach ($resultado['usuarios'] as $usuario) { //enquanto tiver resultados vai mostrando
                    echo "<p>Nome: {$usuario['nome']}</p>";
                }
            }

            echo "<h3>Comunidades Encontradas:</h3>";
            if (empty($resultado['comunidades'])) {
                echo "<p>Nenhuma comunidade encontrada.</p>";
            } else {
                foreach ($resultado['comunidades'] as $comunidade) { // enquanto tiver resultados vai mostrando
                    echo "<p>Nome: {$comunidade['nome']}</p>";
                }
            }
        }
        ?>

        <!-- Formulário de Pesquisa -->
        <form class="d-flex" role="search" method="GET" action="inicial.php"> <!-- 'action' aponta para a mesma página -->
            <input class="form-control me-2" type="search" name="nome" placeholder="Pesquisar amigo" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Buscar</button>
        </form>
    </div>
</nav>


<div class="">

    <div class="row">

        <!-- Informações do perfil -->
        <div class="col-3" >
            <?php $perfil->getPerfil_total(); //chama metodo getPerfil_total() para mostrar todas as informações do pefil do user logado?>

        </div>

        <!-- Seção alterada dinamicamente -->
        <div class="col-5">
            <?php
            $page = $_GET['page'] ?? 'posts'; // Página padrão é 'posts'
            switch ($page) { //seleciona a página desejada
                case 'posts': //caso for a página posts
                    include 'posts.php'; //mostre a parte de posts
                    break; //finaliza
                case 'perfil':  //caso for a página perfil
                    include 'perfil.php'; //mostre a página perfil
                    break; //finaliza
                default: //se der erro
                    echo "<div class='alert alert-danger'>Página não encontrada</div>"; //informação de erro
            }
            ?>
        </div>

        <!-- Amigos e comunidades -->
        <div class="col-4">
            <!-- RESULTADO BUSCA -->
            <div class="card mb-3">
                <div class="card-body">
                    <?php //$busca->getBusca();   ?>
                </div>
            </div>

            <!-- AMIGOS -->
            <div class="card mb-3">
                <div class="card-header">
                    Amigos
                </div>
                <div class="card-body">
                    <?php $perfil->getPerfil_amigos(); ?>
                </div>
            </div>

            <!-- COMUNIDADES -->
            <div class="card">
                <div class="card-header">
                    Comunidades
                </div>
                <div class="card-body">

                    <?php

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entrar_comunidade'])) {
                        $id_usuario_logado = $_SESSION['id_usuario']; // Certifique-se de que o ID do usuário esteja na sessão
                        $id_comunidade = $_POST['id_comunidade'];
                        echo $comunidade->entrarComunidade($id_usuario_logado, $id_comunidade);
                    }

                    $comunidade->mostrarComunidades(); ?>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
