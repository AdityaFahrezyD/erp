<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class OtherExpense extends Model
{
    use HasUuids;
    protected $table = 'other_expenses';
    protected $primaryKey = 'expense_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'type_expense',
        'user_id',
        'fk_project_id',
        'judul_project',
        'nama_pengeluaran',
        'keterangan',
        'jumlah',
        'tanggal',
        'approve_status',
    ];
    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'approve_status' => 'string',
    ];

    public function going_project()
    {
        return $this->belongsTo(GoingProject::class, 'fk_project_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
