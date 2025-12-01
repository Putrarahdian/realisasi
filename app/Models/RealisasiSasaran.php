<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiSasaran extends Model
{
    use HasFactory;
        protected $fillable = [
        'user_id',
        'induk_id',
        'uraian',
        'target',
        'realisasi',
        'capaian',
    ];

    public function user()
    {
        // return $this->belongsTo(RealisasiInduk::class, 'induk_id');
        return $this->belongsTo(User::class, 'user_id');
    }
}
