<?php
$host = '10.10.4.11';
$db = 'agenda_escritorios_oabrj';
$user = 'agenda_escritorios_oabrj_user';
$pass = 'pKF937n*)faa13';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  #http_response_code(500);
  #echo json_encode(['erro' => 'Erro de conexão com o banco de dados']);
  #exit;
  $msg = "ERRO: A conexão com o banco de dados falhou.";
  header("Location: /?tela=post-message&msg=".$msg);
}

// function getHorariosDisponiveis($dataAg, $localAg, $usuario_id){
//   global $pdo;
//   $stmt = $pdo->prepare("
//     SELECT hora 
//     FROM horarios_disponiveis 
//     WHERE hora NOT IN (
//         SELECT hora 
//         FROM agendamentos 
//         WHERE (data = ? AND local = ?) OR (data = ? AND usuario_id = ?)
//     )
//     AND (
//         ? > CURDATE() OR hora >= CURTIME()
//     )
//     ORDER BY hora;
//   ");
//   $stmt->execute([$dataAg, $localAg, $dataAg, $usuario_id, $dataAg]);
//   $horarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
//   return $horarios;
// }

function getHorariosDisponiveis($dataAg, $localAg, $usuario_id){
  global $pdo;

  // A consulta foi reestruturada para usar um JOIN com a tabela 'locais'
  $stmt = $pdo->prepare("
    SELECT h.hora 
    FROM horarios_disponiveis h
    -- NOVO: Conecta com a tabela 'locais' para obter os horários de funcionamento
    JOIN locais l ON l.nome = ? 
    WHERE 
        -- NOVO: Filtra para que a hora seja dentro do intervalo de funcionamento do local
        h.hora >= l.hora_abertura AND h.hora < l.hora_fechamento
        
        -- Condição original para evitar horários já agendados
        AND h.hora NOT IN (
            SELECT a.hora 
            FROM agendamentos a
            WHERE (a.data = ? AND a.local = ?) OR (a.data = ? AND a.usuario_id = ?)
        )
        
        -- Condição original para mostrar apenas horários futuros no dia atual
        AND (? > CURDATE() OR h.hora >= DATE_SUB(CURTIME(), INTERVAL 30 MINUTE))
        
    ORDER BY h.hora;
  ");

  // O array de parâmetros foi atualizado para incluir o $localAg no início para o JOIN
  $stmt->execute([$localAg, $dataAg, $localAg, $dataAg, $usuario_id, $dataAg]);
  
  $horarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
  return $horarios;
}

function getAgendamentosFuturos($dataInicial, $dataFinal, $usuario_id){
  global $pdo;
  $stmt = $pdo->prepare("
    SELECT * 
    FROM agendamentos 
    WHERE data >= ? AND data <= ? 
    AND usuario_id = ? 
    AND CONCAT(data, ' ', hora) > NOW() 
    ORDER BY data, hora;
  ");
  $stmt->execute([$dataInicial, $dataFinal, $usuario_id]);
  $agendamentosFuturos = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $agendamentosFuturos;
}

function getAgendamentosExpirados($usuario_id){
  global $pdo;
  $stmt = $pdo->prepare("
    SELECT * 
    FROM agendamentos 
    WHERE CONCAT(data, ' ', hora) < NOW()  
    AND usuario_id = ? 
    ORDER BY data DESC, hora;
  ");
  $stmt->execute([$usuario_id]);
  $agendamentosExpirados = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $agendamentosExpirados;
}