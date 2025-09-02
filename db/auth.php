<?php
function getUserFake() {
  $data = [];

  $data["ficha"] = 30194535;
  $data["nome"] = "Gabriel Lima";
  $data["email"] = "gabriel.lima@example.com";
  $data["cpf"] = "555.666.777-88";
  $data["dataNascimento"] = "1998-01-20";
  $data["status"] = "ativo";
  $data["adimplenteAnosAnt"] = true;

  return $data;
}

function obterToken() {
  return $_GET['token'] ?? '';
}

function autenticarUsuario($token, $pdo) {
  
  $apiUrl = "https://omega.oabrj.org.br/auth-api/app/auth/validate";
  $oauthKey = "987654321";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'O-AUTH-KEY: $oauthKey'
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'token' => $token
  ]));
  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $log  = "-- INICIO LOG -- ";
  $log .= "HTTP Code: " . $http_code . ", ";
  $log .= "Response: " . $response . ", ";
  $log .= "Token: " . $token;
  $log .= " -- FIM LOG --";
  error_log($log);

  if ($http_code !== 201) {
    return null;
  }

  $data = json_decode($response, true)['userData'];
  // $data = getUserFake();
  
  if (!$data || !isset($data['cpf']) || $data['adimplenteAnosAnt'] == false)
    return null;

  $cpf = $data['cpf'];
  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ?");
  $stmt->execute([$cpf]);
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$usuario) {
    $stmt = $pdo->prepare("INSERT INTO usuarios (ficha, nome, email, cpf, nascimento, status) VALUES (?, ?, ?, ?, ?, 'ativo')");
    $stmt->execute([
      $data['ficha'] ?? '',
      $data['nome'] ?? '',
      $data['email'] ?? '',
      $cpf,
      $data['dataNascimento'] ?? null
    ]);
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ?");
    $stmt->execute([$cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  // MODIFICAÇÃO: Retorna null se o usuário estiver inativo
  if ($usuario && $usuario['status'] === 'inativo') {
      return null;
  }

  return $usuario;
}