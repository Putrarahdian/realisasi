<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetRincian extends Model
{
    protected $table = 'target_rincian';

    protected $fillable = ['target_id','jenis','uraian','target'];

    public function targetHeader()
    {
        return $this->belongsTo(Target::class, 'target_id');
    }
}
