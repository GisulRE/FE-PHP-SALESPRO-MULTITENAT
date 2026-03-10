<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSession extends Model
{
    protected $table = 'whatsapp_sessions';

    protected $fillable = ['company_id', 'session_name', 'is_active', 'session_token'];

    protected $casts = ['is_active' => 'boolean'];
}
