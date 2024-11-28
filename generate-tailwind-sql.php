<?php

// Load the JSON file
$json = file_get_contents('tailwind-classes.json');
$classes = json_decode($json, true);

// Start the INSERT query
$query = "INSERT INTO styles (name, category, rule, data) VALUES\n";

// Build values for each class
$values = [];
foreach ($classes as $class) {
    $name = $class['name'];
    if (!empty($class['rule'])) {
        $rule = $class['rule'];
    } else {
        $rule = null;
    }
    if (!empty($class['category'])) {
        $category = $class['category'];
    } else {
        $category = null;
    }
    $data = json_encode($class['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $values[] = "('$name', '$category', '$rule', '$data')";
}

// Combine values and complete the query
$query .= implode(",\n", $values) . ";";

// Save the query to a file
file_put_contents('tailwind-insert.sql', $query);

print_r("SQL query saved to tailwind-insert.sql");
