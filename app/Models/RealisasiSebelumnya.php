<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiSebelumnya extends Model
{
    use HasFactory;

    protected $table = 'realisasi_sebelumnya';

    protected $fillable = [
        'user_id',
        'tahun_1',
        'tahun_2',
        'target_t1',
        'realisasi_t1',
        'capaian_t1',
        'target_t2',
        'realisasi_t2',
        'capaian_t2',
    ];

    public function user()
    {
        return $this->belongsTo(RealisasiInduk::class, 'induk_id');
    }
}
