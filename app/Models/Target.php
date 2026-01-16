<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;
    
    protected $table = 'target';

    protected $fillable = [
        'tahun',
        'judul',
        'bidang_id',
        'seksi_id',
    ];

    public function realisasiInduks()
    {
        return $this->hasMany(RealisasiInduk::class, 'target_id');
    }
    public function rincian()
    {
        return $this->hasMany(TargetRincian::class, 'target_id');
    }

}
