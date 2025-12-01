<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformasiUmum extends Model
{
    use HasFactory;
        protected $table = 'informasi_umum';

        protected $fillable = [
        'user_id',
        'triwulan',
        'tahun',
        'instansi',
        'penanggung_jawab',
    ];
}
