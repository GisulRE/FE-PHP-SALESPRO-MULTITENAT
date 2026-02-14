<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferRequestLog extends Model
{
    protected $fillable = ['transfer_id', 'user_id', 'action', 'note'];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}