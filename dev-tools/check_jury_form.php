<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

$event = Event::first();

if ($event && $event->juryFormTemplate) {
    echo "✅ Event: {$event->name}\n\n";
    echo "📋 Jury Form Template:\n";
    echo "==========================================\n";
    echo "Use Default Score: " . ($event->juryFormTemplate->use_default_score ? 'Yes' : 'No') . "\n";
    echo "\nCriteria Fields (RAW from DB):\n";
    print_r($event->juryFormTemplate->criteria_fields);

    echo "\n\n📊 Kriteria Details:\n";
    echo "==========================================\n";
    foreach ($event->juryFormTemplate->criteria_fields as $index => $field) {
        echo ($index + 1) . ". {$field['label']}\n";
        echo "   - Type: {$field['type']}\n";
        if (isset($field['max'])) {
            echo "   - Max: {$field['max']} poin\n";
        }
        echo "   - Required: " . (isset($field['required']) && $field['required'] ? 'Yes' : 'No') . "\n";
        echo "\n";
    }

    $totalMax = array_sum(array_column(array_filter($event->juryFormTemplate->criteria_fields, function ($f) {
        return isset($f['max']);
    }), 'max'));
    echo "Total Maksimal: {$totalMax} poin\n";
} else {
    echo "❌ Event atau Jury Form tidak ditemukan.\n";
}
