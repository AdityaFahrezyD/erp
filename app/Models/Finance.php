<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Optional

class Finance extends Model
{
    use HasFactory;
    // use SoftDeletes; // Uncomment if you want to use soft deletes

    protected $fillable = [
        'finance_id',
        'transaction_id',
        'user_id',
        'type',
        'date',
        'description',
        'amount',
        'saldo',
        'status_pembayaran',
        'approve_status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'saldo' => 'decimal:2',
        'status_pembayaran' => 'integer',
        'approve_status' => 'integer',
    ];

    /**
     * Update the saldo (balance) when saving the record
     * This method automatically calculates the balance when a record is created or updated
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($finance) {
            // Get the latest transaction balance regardless of approval status
            $latestTransaction = self::latest()->first();
            $currentBalance = $latestTransaction ? $latestTransaction->saldo : 0;
            
            // Only update the running balance if this record is approved
            if ($finance->approve_status == 1) {
                // Calculate new balance for approved transactions
                $finance->saldo = $currentBalance + $finance->amount;
            } else {
                // For pending/rejected status, maintain the last balance
                $finance->saldo = $currentBalance;
            }
        });

        static::updating(function ($finance) {
            // If approve_status changed to approved (1)
            if ($finance->isDirty('approve_status') && $finance->approve_status == 1) {
                // Get the latest transaction balance before this one
                $latestTransaction = self::where('id', '<', $finance->id)
                    ->latest()
                    ->first();
                
                $currentBalance = $latestTransaction ? $latestTransaction->saldo : 0;
                
                // Calculate new balance
                $finance->saldo = $currentBalance + $finance->amount;
            }
            
            // If amount changed and record is already approved
            if ($finance->isDirty('amount') && $finance->approve_status == 1) {
                // Get the latest transaction balance before this one
                $latestTransaction = self::where('id', '<', $finance->id)
                    ->latest()
                    ->first();
                
                $currentBalance = $latestTransaction ? $latestTransaction->saldo : 0;
                
                // Calculate new balance
                $finance->saldo = $currentBalance + $finance->amount;
            }
            
            // If changing from approved to not approved, we need to recalculate all subsequent records
            if ($finance->isDirty('approve_status') && $finance->getOriginal('approve_status') == 1 && $finance->approve_status != 1) {
                // This will trigger a post-save operation to recalculate balances
                // We'll implement this in the saved event
                $finance->saldo = $finance->getOriginal('saldo') - $finance->amount;
            }
        });
        
        // After a record is updated, we may need to recalculate subsequent records
        static::saved(function ($finance) {
            // Only proceed if approve_status or amount changed
            if ($finance->wasChanged('approve_status') || $finance->wasChanged('amount')) {
                // Get all subsequent records
                $subsequentRecords = self::where('id', '>', $finance->id)
                    ->orderBy('id', 'asc')
                    ->get();
                
                $currentBalance = $finance->saldo;
                
                // Update each subsequent record's balance
                foreach ($subsequentRecords as $record) {
                    if ($record->approve_status == 1) {
                        // Only approved records affect the running balance
                        $currentBalance += $record->amount;
                    }
                    
                    // Update the record's saldo without triggering events
                    self::where('id', $record->id)->update(['saldo' => $currentBalance]);
                }
            }
        });
    }

    /**
     * Get the user that owns this finance record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}