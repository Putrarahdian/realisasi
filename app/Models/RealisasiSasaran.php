<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiSasaran extends Model
{
    use HasFactory;
    protected $fillable = [
        'induk_id', 'user_id', 'uraian',
        'target', 'realisasi', 'capaian',
        'target_tw1','target_tw2','target_tw3','target_tw4',
        'realisasi_tw1','realisasi_tw2','realisasi_tw3','realisasi_tw4',
    ];

    public function user()
    {
        // return $this->belongsTo(RealisasiInduk::class, 'induk_id');
        return $this->belongsTo(User::class, 'user_id');
    }
}
