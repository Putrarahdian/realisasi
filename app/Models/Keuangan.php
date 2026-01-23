<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;
     protected $table = 'keuangan';

    protected $fillable = [
        'tanggal',
        'jenis',              
        'jumlah',
        'keterangan',
        'realisasi_induk_id', 
        'created_by',         
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'decimal:2',
    ];

    public function realisasiInduk()
    {
        // pastikan nama tabel realisasi induk kamu adalah realisasi_induks (sesuai screenshot)
        return $this->belongsTo(RealisasiInduk::class, 'realisasi_induk_id');
    }

    public function creator()
    {
        // tabel user kamu = pengguna, modelnya User
        return $this->belongsTo(User::class, 'created_by');
    }
}
