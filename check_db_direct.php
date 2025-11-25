<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get data langsung dari database
$result = DB::table('jury_form_templates')
    ->where('event_id', 1)
    ->first();

if ($result) {
    echo "Data dari database:\n";
    echo "==================\n";
    $criteriaFields = json_decode($result->criteria_fields, true);

    echo "Raw JSON:\n";
    echo $result->criteria_fields . "\n\n";

    echo "Parsed Data:\n";
    foreach ($criteriaFields as $index => $field) {
        echo ($index + 1) . ". {$field['label']} - Max: " . ($field['max'] ?? 'N/A') . "\n";
    }
} else {
    echo "No data found\n";
}
