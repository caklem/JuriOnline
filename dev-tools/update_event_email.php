<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

$event = Event::first();

if ($event) {
    $event->require_official_email = true;
    $event->allowed_email_domains = 'gmail.com, student.univ.ac.id, company.com';
    $event->save();

    echo "✅ Event '{$event->name}' (ID: {$event->id}) berhasil diupdate!\n";
    echo "   - require_official_email: true\n";
    echo "   - allowed_email_domains: {$event->allowed_email_domains}\n";
} else {
    echo "❌ Tidak ada event yang ditemukan.\n";
}
