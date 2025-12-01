<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiOutput extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'induk_id',
        'triwulan',
        'uraian',
        'target',
        'realisasi',
        'capaian',
        ];
        
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
