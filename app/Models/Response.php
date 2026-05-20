<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $table = 'messages'; // Matches your migration table name
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'report_id',
        'user_id',      // Matches $table->foreignId('user_id')
        'message_text', // Matches $table->text('message_text')
    ];

    // The person who sent the message
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id', 'report_id');
    }
}
