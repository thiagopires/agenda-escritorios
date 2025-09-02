<?php
session_start();
require_once '../db/conexao.php';

// if ((!isset($_POST['action']))) {
//     header('Location: login.php');
//     exit;
// }

$action = (isset($_POST['action'])) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'login':
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
        } else {
            header('Location: login.php?error=1');
        }
        break;

    // --- Ações de Locais ---
    case 'save_local':
        if (!isset($_SESSION['admin_logged_in'])) exit('Acesso negado.');
        $id = $_POST['id'] ?? null;
        $nome = $_POST['nome'] ?? '';
        $hora_abertura = $_POST['hora_abertura'] ?? null;
        $hora_fechamento = $_POST['hora_fechamento'] ?? null;
        if (empty($nome)) break;

        if ($id) {
            $stmt = $pdo->prepare("UPDATE locais SET nome = ?, hora_abertura = ?, hora_fechamento = ? WHERE id = ?");
            $stmt->execute([$nome, $hora_abertura, $hora_fechamento, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO locais (nome) VALUES (?)");
            $stmt->execute([$nome, $hora_abertura, $hora_fechamento]);
        }
        header('Location: index.php?page=locais');
        break;

    case 'delete_local':
        if (!isset($_SESSION['admin_logged_in'])) exit('Acesso negado.');
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM locais WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: index.php?page=locais');
        break;

    // --- Ações de Usuários ---
    case 'update_user_status':
        if (!isset($_SESSION['admin_logged_in'])) exit('Acesso negado.');
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'inativo';
        if ($id) {
            $stmt = $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
        header('Location: index.php?page=usuarios');
        break;

    // --- Ações de Agendamentos ---
    case 'delete_agendamento':
        if (!isset($_SESSION['admin_logged_in'])) exit('Acesso negado.');
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: index.php?page=agendamentos');
        break;
}
exit;