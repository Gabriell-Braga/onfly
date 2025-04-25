<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viagem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome_solicitante',
        'destino',
        'data_ida',
        'data_volta',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
