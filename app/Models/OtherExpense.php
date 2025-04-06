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
        'kategori',
        'keterangan',
        'harga',
        'tanggal_pengeluaran',
        'approve_status',
    ];
}
