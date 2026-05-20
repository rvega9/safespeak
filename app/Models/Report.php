<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $table = 'reports';
    
    // Updated to match your migration primary key
    protected $primaryKey = 'report_id'; 
    
    public $timestamps = true;

    protected $fillable = [
        'user_id',      // NULL for anonymous
        'case_id',      // generated in boot()
        'occurred_at',  // incident date
        'description',
        'status'
    ];

    /**
     * Generate a unique case_id automatically before the report is saved.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            $date = now()->format('Ymd'); 
            $random = strtoupper(Str::random(4)); 
            $report->case_id = "CASE{$date}-{$random}";
        });
    }

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    /**
     * Relationship: The student who owns this report.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relationship: All messages/responses linked to this report.
     * This connects to your Response.php model and 'messages' table.
     */
    public function messages()
    {
        // Using 'report_id' as the foreign key in the messages table
        return $this->hasMany(Response::class, 'report_id', 'report_id')->orderBy('created_at', 'asc');
    }
}