<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiInduk extends Model
{
    use HasFactory;
        protected $fillable = [
            'tahun',
            'bidang_id',
            'seksi_id',
            'user_id',
            'sasaran_strategis',
            'program',
            'indikator',
            'target',
            'hambatan',
            'rekomendasi',
            'tindak_lanjut',
            'dokumen',
            'strategi',
            'alasan',
            'disposisi_kabid',
            'disposisi_kadis',
    ];

    public function outputs() { return $this->hasMany(RealisasiOutput::class, 'induk_id'); }
    public function outcomes() { return $this->hasMany(RealisasiOutcome::class, 'induk_id'); }
    public function sasaran() { return $this->hasOne(RealisasiSasaran::class, 'induk_id'); }
    public function keuangans() { return $this->hasMany(RealisasiKeuangan::class, 'induk_id'); }
    public function keberhasilan() { return $this->hasOne(RealisasiKeberhasilan::class, 'induk_id'); }
    public function bidang() { return $this->belongsTo(Bidang::class); }
    public function seksi() { return $this->belongsTo(Seksi::class); }
    public function user() { return $this->belongsTo(User::class); }

}
