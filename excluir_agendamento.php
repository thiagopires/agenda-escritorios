<?php
require_once 'db/conexao.php';
require_once 'db/auth.php';

# $token = obterToken();

$token = $_POST['token'] ?? '';
$usuario = $token ? autenticarUsuario($token, $pdo) : null;

if ($usuario && isset($_POST['id'])) {
  $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ? AND usuario_id = ?");
  $stmt->execute([$_POST['id'], $usuario['id']]);
}

header("Location: /?token=".htmlspecialchars($token)."&tela=agenda");
exit;
