<?php

// Load the JSON file
$json = file_get_contents('tailwind-classes.json');
$classes = json_decode($json, true);

// Start the INSERT query
$query = "INSERT INTO tailwind_classes (name, rule, data) VALUES\n";

// Build values for each class
$values = [];
foreach ($classes as $class) {
    $name = $class['name'];
    if (!empty($class['rule'])) {
        $rule = $class['rule'];
    } else {
        $rule = null;
    }
    $data = json_encode($class['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $values[] = "('$name', '$rule', '$data')";
}

// Combine values and complete the query
$query .= implode(",\n", $values) . ";";

// Save the query to a file
file_put_contents('tailwind-insert.sql', $query);

print_r("SQL query saved to tailwind-insert.sql");
