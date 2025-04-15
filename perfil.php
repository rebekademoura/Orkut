<?php require_once 'comunidade.php'; ?>

<h3>Solicitações de Amizade</h3>
<?php $perfil->getSolicitacoesAmizade($_SESSION['id_usuario']); ?>

<h3>Meus Amigos</h3>
<?php $perfil->getAmigos($_SESSION['id_usuario']); ?>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Adicionar comunidade
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="inicial.php">
                <div class="mb-3">
                    <label for="nome_comunidade" class="form-label">Nome da Comunidade</label>
                    <input type="text" class="form-control" id="nome_comunidade" name="nome_comunidade" required>
                </div>
                <div class="mb-3">
                    <label for="descricao_comunidade" class="form-label">Descrição</label>
                    <textarea class="form-control" id="descricao_comunidade" name="descricao_comunidade" required></textarea>
                </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" name="adicionar_comunidade" class="btn btn-primary">Adicionar Comunidade</button>
            </div>
            </form>
        </div>
    </div>
</div>



<!-- Formulário -->


<div class="mt-5">
    <h3>Minhas comunidades</h3>
    <?php $comunidade->mostrarComunidadesParticipante($_SESSION['id_usuario']); ?>
</div>
