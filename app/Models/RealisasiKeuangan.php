<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiKeuangan extends Model
{
    use HasFactory;

    protected $table = 'realisasi_keuangans'; // pastikan sesuai nama tabel

    protected $fillable = [
        'user_id',
        'induk_id',
        'triwulan',
        'target',
        'realisasi',
        'capaian',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function induk()
    {
        return $this->belongsTo(RealisasiInduk::class, 'induk_id');
    }
}
