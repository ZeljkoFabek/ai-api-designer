<?php

require_once 'function.php';

$data = json_decode(file_get_contents('sample.json'), true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
            <h2>AI API Designer</h2>

            <?php if(isset($data['description'])):?>
                <h4>Description</h4>
                <p><?= $data['description']?></p>
            <?php endif;?>

            <?php if(isset($data['api_endpoints']) && is_array($data['api_endpoints'])):?>
                <h4>API Endpoints</h4>
                <?php foreach($data['api_endpoints'] as $api): ?>
                    <div class="card mb-2 p-2">
                        <b><?= $api['method'] ?></b> <?= $api['path'] ?><br>
                        <small><?= $api['description'] ?></small>

                        <?php if(isset($api['example_request_body'])): ?>
                            <pre><?= json_encode($api['example_request_body'], JSON_PRETTY_PRINT) ?></pre>
                        <?php endif; ?>

                        <?php if(isset($api['example_response'])): ?>
                            <pre><?= json_encode($api['example_response'], JSON_PRETTY_PRINT) ?></pre>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif;?>

        </div>
</body>
</html>