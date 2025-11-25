<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuryFormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'criteria_fields',
        'use_default_score',
    ];

    protected $casts = [
        'criteria_fields' => 'array',
        'use_default_score' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function juryScores()
    {
        return $this->hasMany(JuryScore::class);
    }

    // Template form penjurian yang umum digunakan
    public static function getTemplates()
    {
        return [
            'competition_basic' => [
                'name' => 'Kompetisi Dasar',
                'use_default_score' => true,
                'fields' => [
                    ['label' => 'Kesesuaian Tema', 'type' => 'score', 'max' => 25, 'required' => true],
                    ['label' => 'Kreativitas', 'type' => 'score', 'max' => 25, 'required' => true],
                    ['label' => 'Teknis/Execution', 'type' => 'score', 'max' => 25, 'required' => true],
                    ['label' => 'Presentasi', 'type' => 'score', 'max' => 25, 'required' => true],
                ]
            ],
            'presentation' => [
                'name' => 'Presentasi/Pidato',
                'use_default_score' => true,
                'fields' => [
                    ['label' => 'Materi/Konten', 'type' => 'score', 'max' => 30, 'required' => true],
                    ['label' => 'Penyampaian', 'type' => 'score', 'max' => 30, 'required' => true],
                    ['label' => 'Penampilan', 'type' => 'score', 'max' => 20, 'required' => true],
                    ['label' => 'Waktu', 'type' => 'score', 'max' => 20, 'required' => true],
                ]
            ],
            'performance' => [
                'name' => 'Performance/Penampilan',
                'use_default_score' => true,
                'fields' => [
                    ['label' => 'Teknik', 'type' => 'score', 'max' => 30, 'required' => true],
                    ['label' => 'Ekspresi/Penghayatan', 'type' => 'score', 'max' => 30, 'required' => true],
                    ['label' => 'Kostum/Penampilan', 'type' => 'score', 'max' => 20, 'required' => true],
                    ['label' => 'Kekompakan (untuk tim)', 'type' => 'score', 'max' => 20, 'required' => false],
                ]
            ],
            'design' => [
                'name' => 'Desain/Karya Seni',
                'use_default_score' => true,
                'fields' => [
                    ['label' => 'Originalitas', 'type' => 'score', 'max' => 25, 'required' => true],
                    ['label' => 'Komposisi', 'type' => 'score', 'max' => 25, 'required' => true],
                    ['label' => 'Teknik', 'type' => 'score', 'max' => 25, 'required' => true],
                    ['label' => 'Pesan/Makna', 'type' => 'score', 'max' => 25, 'required' => true],
                ]
            ],
            'research' => [
                'name' => 'Penelitian/Karya Ilmiah',
                'use_default_score' => true,
                'fields' => [
                    ['label' => 'Metodologi', 'type' => 'score', 'max' => 30, 'required' => true],
                    ['label' => 'Analisis Data', 'type' => 'score', 'max' => 30, 'required' => true],
                    ['label' => 'Kesimpulan', 'type' => 'score', 'max' => 20, 'required' => true],
                    ['label' => 'Referensi', 'type' => 'score', 'max' => 20, 'required' => true],
                ]
            ],
        ];
    }
}
