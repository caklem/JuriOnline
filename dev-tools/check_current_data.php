<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING CURRENT DATABASE DATA ===\n\n";

$form = App\Models\JuryFormTemplate::where('event_id', 1)->first();

if ($form) {
    echo "Form ID: " . $form->id . "\n";
    echo "Event ID: " . $form->event_id . "\n";
    echo "Updated at: " . $form->updated_at . "\n\n";

    echo "Criteria Fields (JSON):\n";
    echo json_encode($form->criteria_fields, JSON_PRETTY_PRINT) . "\n\n";

    echo "Individual criteria:\n";
    foreach ($form->criteria_fields as $index => $field) {
        echo ($index + 1) . ". " . $field['label'] . " - Max: " . ($field['max'] ?? 'N/A') . "\n";
    }

    $total = array_sum(array_column($form->criteria_fields, 'max'));
    echo "\nTotal Max: " . $total . "\n";
} else {
    echo "No form found for event_id = 1\n";
}
