<?php
// Inclui o cabeçalho, que já inicia a sessão
require_once 'partials/header.php';

// Se o admin já estiver logado, redireciona para o painel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title h5 text-center">Login do Administrador</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger mt-3">
                        Usuário ou senha inválidos.
                    </div>
                <?php endif; ?>

                <form method="post" action="actions.php">
                    <input type="hidden" name="action" value="login">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">Entrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'partials/footer.php'; ?>