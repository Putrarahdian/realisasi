<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiKeberhasilan extends Model
{
    use HasFactory;
    
    protected $table = 'realisasi_keberhasilan';

    protected $fillable = [
        'induk_id',
        'user_id',
        'keberhasilan', 
        'hambatan',
        'keberhasilan_tw1',
        'keberhasilan_tw2',
        'keberhasilan_tw3',
        'keberhasilan_tw4',
        'hambatan_tw1',
        'hambatan_tw2',
        'hambatan_tw3',
        'hambatan_tw4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
