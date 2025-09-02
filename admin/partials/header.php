<?php
session_start();
require_once '../db/conexao.php';

// Redireciona para o login se não estiver autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (basename($_SERVER['PHP_SELF']) != 'login.php') {
        header('Location: login.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Agendamento OAB RJ</title>
  <link href="../bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <header class="mb-4 d-flex justify-content-between align-items-center">
      <h1 class="h3">OABRJ - Agendamento de Escritórios [Admin]</h1>
      <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        <a href="logout.php" class="btn btn-outline-secondary">Sair</a>
      <?php endif; ?>
    </header>

    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
    <nav class="mb-4 d-flex justify-content-center gap-2">
        <a href="index.php?page=agendamentos" class="btn btn-outline-danger <?= ($_GET['page'] ?? 'agendamentos') == 'agendamentos' ? 'active' : '' ?>">Agendamentos</a>
        <a href="index.php?page=locais" class="btn btn-outline-danger <?= ($_GET['page'] ?? '') == 'locais' ? 'active' : '' ?>">Locais</a>
        <a href="index.php?page=usuarios" class="btn btn-outline-danger <?= ($_GET['page'] ?? '') == 'usuarios' ? 'active' : '' ?>">Usuários</a>
    </nav>
    <?php endif; ?>