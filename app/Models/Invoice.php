<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Invoice extends Model
{
    use HasUuids;
    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'project_id',
        'recipient',
        'company',
        'information',
        'invoice_amount',
        'recipient_email',
        'is_repeat',
        'send_date',
        'send_at',
        'approve_status',
    ];

    public function project()
    {
        return $this->belongsTo(GoingProject::class, 'project_id', 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
