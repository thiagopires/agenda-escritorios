<?php
require_once 'db/conexao.php';
require_once 'db/auth.php';

$data = $_POST['data'] ?? '';
$hora = $_POST['hora'] ?? '';
$local = $_POST['local'] ?? '';
$usuario_id = $_POST['usuario_id'] ?? '';
$token = $_POST['token'] ?? '';

// MODIFICAÇÃO: Verifica se o usuário está autenticado e ativo antes de salvar
$usuario = $token ? autenticarUsuario($token, $pdo) : null;
if (!$usuario || $usuario['id'] != $usuario_id) {
    $msg = "ERRO: Autenticação falhou ou usuário inativo.";
    header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
    exit;
}

if (!$data || !$hora || !$local || !$usuario_id) {
  $msg = "ERRO: Campos obrigatórios não enviados.";
  header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
  exit;
}

// --- INÍCIO DAS NOVAS REGRAS ---

// REGRA 1: Não permitir agendar aos finais de semana
// Criamos um objeto DateTime com a data recebida para facilitar a manipulação.
// O formato 'N' retorna o dia da semana como um número (1 para Segunda, ..., 6 para Sábado, 7 para Domingo).
try {
    $diaDaSemana = (new DateTime($data))->format('N');
    if ($diaDaSemana >= 6) { // Se for 6 (Sábado) ou 7 (Domingo)
        $msg = "ERRO: Não é permitido realizar agendamentos aos finais de semana.";
        header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
        exit;
    }
} catch (Exception $e) {
    // Captura o erro caso a data enviada seja inválida
    $msg = "ERRO: O formato da data fornecida é inválido.";
    header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
    exit;
}

// REGRA 2: Máximo de 2 agendamentos por dia por usuário.
$stmtVerificaDia = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE usuario_id = ? AND data = ?");
$stmtVerificaDia->execute([$usuario_id, $data]);
$agendamentosNoDia = $stmtVerificaDia->fetchColumn();

if ($agendamentosNoDia >= 2) {
    $msg = "ERRO: Você atingiu o limite de 2 agendamentos para esta data.";
    header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
    exit;
}

// REGRA 3: Máximo de 4 agendamentos por semana por usuário.
// Usamos a função YEARWEEK() do SQL para agrupar por semana (modo 1: semana começa na Segunda).
$stmtVerificaSemana = $pdo->prepare(
    "SELECT COUNT(*) FROM agendamentos WHERE usuario_id = ? AND YEARWEEK(data, 1) = YEARWEEK(?, 1)"
);
$stmtVerificaSemana->execute([$usuario_id, $data]);
$agendamentosNaSemana = $stmtVerificaSemana->fetchColumn();

if ($agendamentosNaSemana >= 4) {
    $msg = "ERRO: Você atingiu o limite de 4 agendamentos para esta semana.";
    header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
    exit;
}

// --- FIM DAS NOVAS REGRAS ---

$stmt = $pdo->prepare("INSERT INTO agendamentos (usuario_id, data, hora, local) VALUES (?, ?, ?, ?)");

if ($stmt->execute([$usuario_id, $data, $hora, $local])) {
  $msg = "Agendamento realizado com sucesso.";
} else {
  // Adicionamos uma verificação mais robusta do erro, caso não seja por duplicidade
  $errorInfo = $stmt->errorInfo();
  // O código de erro '23000' (ou 1062 para MySQL) geralmente indica violação de chave única (horário duplicado)
  if ($errorInfo[1] == 1062) {
      $msg = "ERRO: Este horário já está ocupado. Por favor, escolha outro.";
  } else {
      $msg = "ERRO: Não foi possível realizar o agendamento.";
      error_log("ERRO: " . $errorInfo[2]);
  }
};

header("Location: /?token=".htmlspecialchars($token)."&tela=post-message&msg=".urlencode($msg));
exit;