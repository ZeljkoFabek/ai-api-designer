<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

function generateSQL($data) {
    $sql = "";

    foreach ($data['database_schema'] as $table) {
        $sql .= "CREATE TABLE " . $table['table'] . " (\n";

        $fields = [];

        foreach ($table['fields'] as $field) {
            $line = "  " . $field['name'] . " " . $field['type'];

            if ($field['name'] === 'id') {
                $line .= " PRIMARY KEY";
            }

            $fields[] = $line;
        }

        $sql .= implode(",\n", $fields);
        $sql .= "\n);\n\n";
    }

    return $sql;
}

echo json_encode([
    "sql" => trim(generateSQL($data))
]);