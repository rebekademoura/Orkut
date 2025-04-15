<?php

session_start();

class Usuario {
    public function logout() {
        // Destroi a sessão
        session_unset();
        session_destroy();

        // Redireciona para a página de login
        header('Location: login_cadastro.php');
        exit;
    }
}

$usuario = new Usuario();
$usuario->logout();

?>

