<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kegiatan',
        'anggaran',
        'realisasi',
        'tanggal',
        'keterangan',
        'selisih',
        'user_id',
        'waktu_pelaksanaan',
        'target',
        'capaian',
    ];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
