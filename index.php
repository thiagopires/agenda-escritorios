<?php
require_once 'db/conexao.php';
require_once 'db/auth.php';

$token = obterToken();
if (!isset($_GET['tela'])){
  header("Location: /?token=".htmlspecialchars($token)."&tela=login");
}
$usuario = $token ? autenticarUsuario($token, $pdo) : null;

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Agendamento OAB RJ</title>
  <link href="bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <header class="mb-4 text-center">
      <h1 class="h3">OABRJ - Agendamento de Escritórios</h1>
    </header>

  <nav class="mb-4 d-flex justify-content-center gap-2">
  <a href="?token=<?= htmlspecialchars($token) ?>&tela=login" class="btn btn-outline-danger <?= $_GET['tela'] == 'login' ? 'active' : '' ?>">Início</a>
  <a href="?token=<?= htmlspecialchars($token) ?>&tela=agenda" class="btn btn-outline-danger <?= $_GET['tela'] == 'agenda' ? 'active' : '' ?>">Meus Agendamentos</a>
  <a href="?token=<?= htmlspecialchars($token) ?>&tela=cadastro" class="btn btn-outline-danger <?= $_GET['tela'] == 'cadastro' ? 'active' : '' ?>">Novo Agendamento</a>
  </nav>

    <?php if ($_GET['tela'] == 'post-message'): ?>
      <div class="alert alert-light">
        <h2 class="h5"><?= htmlspecialchars($_GET['msg'] ?? '') ?></h2>
      </div>
    <?php endif; ?>

    <?php if ($_GET['tela'] == 'login'): ?>
      <div class="card">
        <div class="card-body">
          <h2 class="card-title h5">Início</h2>
          <?php if ($usuario): ?>
            <p>Bem-vindo(a), <strong><?= htmlspecialchars($usuario['nome']) ?></strong></p>
            <ul class="list-group">
              <li class="list-group-item"><strong>Ficha:</strong> <?= htmlspecialchars($usuario['ficha']) ?></li>
              <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></li>
              <li class="list-group-item"><strong>CPF:</strong> <?= htmlspecialchars($usuario['cpf']) ?></li>
              <li class="list-group-item"><strong>Nascimento:</strong> <?= htmlspecialchars(date('d/m/Y',strtotime($usuario['nascimento']))) ?></li>
              <li class="list-group-item"><strong>Status:</strong> <span class="badge <?= $usuario['status'] == 'ativo' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= htmlspecialchars(ucfirst($usuario['status'])) ?></span></li>
            </ul>
          <?php else: ?>
            <div class="alert alert-danger mt-3">Token inválido, expirado ou usuário inativo.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($_GET['tela'] == 'agenda'): ?>
      <div class="card">
        <div class="card-body">
          <h2 class="card-title h5">Meus Agendamentos</h2>
          <?php if ($usuario): ?>
            <?php
              $dataInicial = $_GET['dataInicial'] ?? date('Y-m-d');
              $dataFinal = $_GET['dataFinal'] ?? date('Y-m-d', strtotime('+365 days'));
              echo '<form method="get" class="mb-3 row g-2">';
              echo '<input type="hidden" name="tela" value="agenda">';
              echo '<input type="hidden" name="token" value="'. htmlspecialchars($token) .'">';
              echo '<div class="col-auto"><label class="form-label">Intervalo:</label></div>';
              echo '<div class="col-auto">';
              echo '<input type="date" name="dataInicial" class="form-control" min="'. date('Y-m-d') .'" value="' . htmlspecialchars($dataInicial) . '" onchange="this.form.submit()">';
              echo '</div>';
              echo '<div class="col-auto"><label class="form-label">até</label></div>';
              echo '<div class="col-auto">';
              echo '<input type="date" name="dataFinal"   class="form-control" min="'. date('Y-m-d') .'" value="' . htmlspecialchars($dataFinal) . '" onchange="this.form.submit()">';
              echo '</div>';
              echo '</form>';

              $agendamentosFuturos = getAgendamentosFuturos($dataInicial, $dataFinal, $usuario['id']);

              echo "<h5 class='mt-4'>Futuros:</h5>";
              if (!$agendamentosFuturos) {
                echo '<p class="text-muted">Nenhum agendamento encontrado.</p>';
              } else {
                echo '<div class="list-group">';
                foreach ($agendamentosFuturos as $a) {
                  echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                  echo '<div>';
                  echo '<strong>Data:</strong> ' . htmlspecialchars(date('d/m/Y',strtotime($a['data']))) . '<br>';
                  echo '<strong>Horário:</strong> ' . htmlspecialchars($a['hora']) . '<br>';
                  echo '<strong>Local:</strong> ' . htmlspecialchars($a['local']);
                  echo '</div>';
                  echo '<form method="post" action="excluir_agendamento.php" onsubmit="return confirm(\'Deseja realmente excluir este agendamento?\')">';
                  echo '<input type="hidden" name="id" value="' . htmlspecialchars($a['id']) . '">';
                  echo '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '">';
                  echo '<button type="submit" class="btn btn-sm btn-outline-danger">🗑 Excluir</button>';
                  echo '</form>';
                  echo '</div>';
                }
                echo '</div>';
              }

              $agendamentosExpirados = getAgendamentosExpirados($usuario['id']);

              echo "<h5 class='mt-4'>Expirados:</h5>";
              if (!$agendamentosExpirados) {
                echo '<p class="text-muted">Nenhum agendamento encontrado.</p>';
              } else {
                echo '<div class="list-group">';
                foreach ($agendamentosExpirados as $a) {
                  echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                  echo '<div>';
                  echo '<strong>Data:</strong> ' . htmlspecialchars(date('d/m/Y',strtotime($a['data']))) . '<br>';
                  echo '<strong>Horário:</strong> ' . htmlspecialchars($a['hora']) . '<br>';
                  echo '<strong>Local:</strong> ' . htmlspecialchars($a['local']);
                  echo '</div>';
                  echo '</div>';
                }
                echo '</div>';
              }
            ?>
          <?php else: ?>
            <div class="alert alert-danger">Você precisa estar autenticado para ver a agenda.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($_GET['tela'] == 'cadastro'): ?>
      <div class="card">
        <div class="card-body">
          <h2 class="card-title h5">Novo Agendamento</h2>
          <?php if ($usuario): ?>
            <?php
              $dataAg = $_GET['data'] ?? '';
              $localAg = $_GET['local'] ?? '';
            ?>
            <form method="get" class="row g-3 mb-4">
              <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
              <input type="hidden" name="tela" value="cadastro">

              <div class="col-md-6">
                <label class="form-label">Data</label>
                <input type="date" name="data" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($dataAg) ?>" onchange="this.form.submit()" required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Escritório</label>
                <select name="local" class="form-select" onchange="this.form.submit()" required>
                  <option value="">-- selecione --</option>
                  <?php 
                    // MODIFICAÇÃO: Carrega locais do banco
                    $stmt = $pdo->query("SELECT nome FROM locais ORDER BY nome");
                    $locais = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($locais as $local){
                      echo '<option value="'.htmlspecialchars($local).'" '.($localAg == $local ? 'selected' : '').'>'.htmlspecialchars($local).'</option>';
                    }
                  ?>
                </select>
              </div>
            </form>

            <?php if ($dataAg && $localAg): ?>
              <form method="post" action="salvar_agendamento.php">
                <input type="hidden" name="usuario_id" value="<?= htmlspecialchars($usuario['id']) ?>">
                <input type="hidden" name="data" value="<?= htmlspecialchars($dataAg) ?>">
                <input type="hidden" name="local" value="<?= htmlspecialchars($localAg) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="mb-3">
                  <label class="form-label">Horários Disponíveis</label><br>
                  <div class="d-flex flex-wrap gap-2" aria-label="Horários disponíveis">
                    <?php
                      $horarios = getHorariosDisponiveis($dataAg, $localAg, $usuario['id']);
                      if (!$horarios) {
                        echo "<p class='text-muted'>Nenhum horário disponível para esta data/local.</p>";
                      } else {
                        foreach ($horarios as $index => $h) {
                          $id = 'hora_' . $index;
                          echo '<input type="radio" class="btn-check" name="hora" id="' . $id . '" value="' . htmlspecialchars($h) . '" required>';
                          echo '<label class="btn btn-outline-danger" for="' . $id . '">' . htmlspecialchars($h) . '</label>';
                        }
                      }
                    ?>
                  </div>
                </div>
                <?php if ($horarios): ?>
                <button type="submit" class="btn btn-danger">Salvar Agendamento</button>
                <?php endif; ?>
              </form>
            <?php endif; ?>

          <?php else: ?>
            <div class="alert alert-danger">Você precisa estar autenticado e ativo para cadastrar agendamentos.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>