<?php
require_once 'partials/header.php';

$page = $_GET['page'] ?? 'agendamentos';

switch ($page) {
    case 'locais':
        $stmt = $pdo->query("SELECT * FROM locais ORDER BY nome");
        $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="card">
            <div class="card-body">
                <h2 class="card-title h5">Gerenciar Locais</h2>
                <a href="?page=form_local" class="btn btn-danger mb-3">Adicionar Novo Local</a>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locais as $local): ?>
                            <tr>
                                <td><?= htmlspecialchars($local['id']) ?></td>
                                <td><?= htmlspecialchars($local['nome']) ?></td>
                                <td>
                                    <a href="?page=form_local&id=<?= $local['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <a href="actions.php?action=delete_local&id=<?= $local['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        break;

        case 'form_local':
            $local = null;
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM locais WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $local = $stmt->fetch(PDO::FETCH_ASSOC);
            }
    
            // Busca todos os horários disponíveis
            // $stmtHorarios = $pdo->query("SELECT hora FROM horarios_disponiveis ORDER BY hora");
            // $horarios_disponiveis = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
            $horarios_disponiveis = array('08:00:00','09:00:00','10:00:00','11:00:00','12:00:00','13:00:00','14:00:00','15:00:00','16:00:00','17:00:00','18:00:00');

            ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5"><?= $local ? 'Editar' : 'Adicionar' ?> Local</h2>
                    <form action="actions.php" method="post">
                        <input type="hidden" name="action" value="save_local">
                        <input type="hidden" name="id" value="<?= $local['id'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Local</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($local['nome'] ?? '') ?>" required>
                        </div>
    
                        <div class="mb-3">
                            <label for="hora_abertura" class="form-label">Hora de Abertura</label>
                            <select class="form-select" id="hora_abertura" name="hora_abertura" required>
                                <option value="">Selecione</option>
                                <?php foreach ($horarios_disponiveis as $horario): ?>
                                    <option 
                                        value="<?= htmlspecialchars($horario) ?>"
                                        <?= isset($local['hora_abertura']) && $local['hora_abertura'] == $horario ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(substr($horario, 0, 5)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
    
                        <div class="mb-3">
                            <label for="hora_fechamento" class="form-label">Hora de Fechamento</label>
                            <select class="form-select" id="hora_fechamento" name="hora_fechamento" required>
                                <option value="">Selecione</option>
                                <?php foreach ($horarios_disponiveis as $horario): ?>
                                    <option 
                                        value="<?= htmlspecialchars($horario) ?>"
                                        <?= isset($local['hora_fechamento']) && $local['hora_fechamento'] == $horario ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(substr($horario, 0, 5)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
    
                        <button type="submit" class="btn btn-danger">Salvar</button>
                        <a href="index.php?page=locais" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
            <?php
            break;

    case 'usuarios':
        $stmt = $pdo->query("SELECT id, nome, email, cpf, status FROM usuarios ORDER BY nome");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
         ?>
        <div class="card">
            <div class="card-body">
                <h2 class="card-title h5">Gerenciar Usuários</h2>
                 <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>CPF</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['cpf']) ?></td>
                                <td>
                                    <span class="badge <?= $usuario['status'] == 'ativo' ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= ucfirst($usuario['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?page=form_usuario&id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-primary">Editar Status</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'form_usuario':
        $usuario = null;
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT id, nome, status FROM usuarios WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$usuario) { echo 'Usuário não encontrado.'; break; }
        ?>
        <div class="card">
             <div class="card-body">
                <h2 class="card-title h5">Editar Status de <?= htmlspecialchars($usuario['nome']) ?></h2>
                <form action="actions.php" method="post">
                    <input type="hidden" name="action" value="update_user_status">
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="ativo" <?= $usuario['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= $usuario['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger">Salvar</button>
                    <a href="index.php?page=usuarios" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
        <?php
        break;
        
    case 'agendamentos':
    default:
        $query = "
            SELECT a.id, a.data, a.hora, a.local, u.nome as usuario_nome, u.cpf
            FROM agendamentos a
            JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.data DESC, a.hora DESC
        ";
        $stmt = $pdo->query($query);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="card">
            <div class="card-body">
                <h2 class="card-title h5">Todos os Agendamentos</h2>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>CPF</th>
                                <th>Local</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agendamentos)): ?>
                                <tr><td colspan="6" class="text-center">Nenhum agendamento encontrado.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($agendamentos as $ag): ?>
                            <tr>
                                <td><?= htmlspecialchars($ag['usuario_nome']) ?></td>
                                <td><?= htmlspecialchars($ag['cpf']) ?></td>
                                <td><?= htmlspecialchars($ag['local']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($ag['data']))) ?></td>
                                <td><?= htmlspecialchars($ag['hora']) ?></td>
                                <td>
                                    <a href="actions.php?action=delete_agendamento&id=<?= $ag['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir este agendamento?')">🗑 Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        break;
}

require_once 'partials/footer.php';