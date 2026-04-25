<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-type: application/json; charset=utf-8');
  
  // LM Studio API Key
  $AUTHORIZATION = 'sk-lm-xPDxMIan:hz3isimadnThLjhLjGGg';

  // LM Studio REST API endpoint
  $url = "http://localhost:1234/v1/models";

  // PHP Curl
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer " . $AUTHORIZATION,
      "Content-type: application/json; charset=utf-8"
  ]);

  $response    = curl_exec($ch);
  $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err         = curl_error($ch);

  curl_close($ch);

  if(curl_errno($ch)){
    echo json_encode([
        "error" => "LM Studio not responding",
        "details" => curl_error($ch)
    ]);
    exit;
}

  if ($httpCode !== 200) {
      http_response_code(502);
      echo json_encode(['status' => 'error', 'message' => "LM‑Studio error: $httpCode – $err"]);
      exit;
  }

  echo $response;