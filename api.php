<?php
  // Reservation system for booking appointments
  header('Access-Control-Allow-Origin: *');
  header('Content-type: application/json; charset=utf-8');
  ini_set('max_execution_time', '600');
  
  $input = json_decode(file_get_contents('php://input'), true);

  // LM Studio API Key
  $AUTHORIZATION = 'sk-lm-xPDxMIan:hz3isimadnThLjhLjGGg';

  // LM Studio System Prompt
  $systemPrompt ='You are a senior software architect.
  Your task is to design a backend system based on the user request.

  IMPORTANT:
- Return ONLY valid raw JSON
- DO NOT include markdown formatting
- DO NOT use ```json or ```
- DO NOT include any explanations
- DO NOT include text before or after JSON
- Output must start with { and end with }

  Return the result in STRICT JSON format with the following structure:
  {
    "description": "Short system description",
    "api_endpoints": [
      {
        "method": "GET/POST/PUT/DELETE",
        "path": "/example",
        "description": "What it does"
      }
    ],
    "database_schema": [
      {
        "table": "table_name",
        "fields": [
          {"name": "id", "type": "INT", "description": "Primary key"},
          {"name": "name", "type": "VARCHAR", "description": "Example field"}
        ]
      }
    ]
  }

  Add also:
  - example_request_body for POST
  - example_response for GET

  Rules:
  - Keep it simple and realistic
  - Max 5 API endpoints
  - Max 2 tables
  - Do not include explanations outside JSON
  - Use clear naming';

  // LM Studio User Prompt
  $userPrompt = 'User request: ' . $input['prompt'];


  // LM Studio REST API endpoint
  $url = "http://localhost:1234/v1/chat/completions";

  // LM Studio JSON Strocture
  $data = [

    "model" => "local-model",
    "messages" => [
        [
            "role" => "system",
            "content" => $systemPrompt
        ],
        [
            "role" => "user",
            "content" => $userPrompt
        ]
    ],
    "max_tokens" => 1200,
    "temperature" => 0.1
    
  ];

  // PHP Curl
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer " . $AUTHORIZATION,
      "Content-Type: application/json"
  ]);

  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

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

  $result = json_decode($response, true);

  // extract AI content
  $content = $result['choices'][0]['message']['content'] ?? '';

  $content = trim($content);
  
  echo $content;