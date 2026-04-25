<?php

function show($stuff)
{
	echo "<pre>";
	print_r($stuff);
	echo "</pre>";
}

function esc($str):string
{
	return htmlspecialchars($str);
}

function generateSQL(array $data):String {
    
    $sql = "";

    if(isset($data['database_schema'])) {
        foreach ($data['database_schema'] as $table) {
            $sql .= "CREATE TABLE " . $table['table'] . " (\n";

            $fields = [];

            foreach ($table['fields'] as $field) {
                $line = "  " . $field['name'] . " " . $field['type'];

                // ako je ID → dodaj PRIMARY KEY
                if ($field['name'] === 'id') {
                    $line .= " PRIMARY KEY";
                }

                $fields[] = $line;
            }

            $sql .= implode(",\n", $fields);
            $sql .= "\n);\n\n";
        }
    }

    return $sql;
}