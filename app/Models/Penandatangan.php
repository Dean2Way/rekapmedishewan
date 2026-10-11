<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penandatangan extends Model
{
    protected $table = 'penandatangan';
    protected $primaryKey = 'id_penandatangan';

    protected $fillable = ['nama', 'nip', 'jabatan'];
}
