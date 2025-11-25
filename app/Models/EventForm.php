<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'form_fields',
    ];

    protected $casts = [
        'form_fields' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Template form yang umum digunakan
    public static function getTemplates()
    {
        return [
            'basic' => [
                'name' => 'Form Dasar',
                'fields' => [
                    ['label' => 'Nomor Telepon', 'type' => 'text', 'required' => true],
                    ['label' => 'Alamat Lengkap', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Motivasi Mengikuti Event', 'type' => 'textarea', 'required' => true],
                ]
            ],
            'competition' => [
                'name' => 'Form Kompetisi',
                'fields' => [
                    ['label' => 'Nomor Telepon', 'type' => 'text', 'required' => true],
                    ['label' => 'Institusi/Asal Sekolah/Universitas', 'type' => 'text', 'required' => true],
                    ['label' => 'Nim/Nis', 'type' => 'text', 'required' => false],
                    ['label' => 'Alamat Lengkap', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Pengalaman Sebelumnya', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Link Portofolio (jika ada)', 'type' => 'url', 'required' => false],
                ]
            ],
            'seminar' => [
                'name' => 'Form Seminar/Workshop',
                'fields' => [
                    ['label' => 'Nomor Telepon', 'type' => 'text', 'required' => true],
                    ['label' => 'Pekerjaan/Status', 'type' => 'select', 'options' => ['Pelajar', 'Mahasiswa', 'Karyawan', 'Wiraswasta', 'Lainnya'], 'required' => true],
                    ['label' => 'Institusi/Perusahaan', 'type' => 'text', 'required' => true],
                    ['label' => 'Harapan Dari Event Ini', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Pertanyaan Untuk Pembicara', 'type' => 'textarea', 'required' => false],
                ]
            ],
            'team' => [
                'name' => 'Form Tim/Kelompok',
                'fields' => [
                    ['label' => 'Nama Tim', 'type' => 'text', 'required' => true],
                    ['label' => 'Nomor Telepon Ketua', 'type' => 'text', 'required' => true],
                    ['label' => 'Jumlah Anggota Tim', 'type' => 'number', 'required' => true],
                    ['label' => 'Daftar Anggota Tim (Nama & Email)', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Institusi/Asal', 'type' => 'text', 'required' => true],
                ]
            ],
        ];
    }
}
