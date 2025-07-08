<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubModul extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sub_modul';
    protected $fillable = [
        'modul_id',
        'batas_awal',
        'batas_akhir',
        'nama_sub_modul',
        'deskripsi_sub_modul',
        'status', // Missing in your model
        'optimistic_time',
        'most_likely_time',
        'pessimistic_time',
        'expected_time',
        'variance',
        'est', // Earliest Start Time
        'eft', // Earliest Finish Time
        'lst', // Latest Start Time
        'lft', // Latest Finish Time
        'total_float', // Total Float/Slack
        'is_critical_path',
        'dependencies',
    ];
    protected $casts = [
        'batas_awal' => 'date',
        'batas_akhir' => 'date',
        'optimistic_time' => 'integer',
        'most_likely_time' => 'integer',
        'pessimistic_time' => 'integer',
        'expected_time' => 'decimal:2',
        'variance' => 'decimal:4',
        'est' => 'decimal:2',
        'eft' => 'decimal:2',
        'lst' => 'decimal:2',
        'lft' => 'decimal:2',
        'total_float' => 'decimal:2',
        'is_critical_path' => 'boolean',
    ];


    public $incrementing = false;
    protected $keyType = 'string';


    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class, 'project_staff', 'sub_modul_id', 'id_pegawai')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function sub_modul()
    {
        return $this->belongsTo(ProjectModul::class, 'modul_id');
    }

    public function staff()
    {
        return $this->hasMany(ProjectStaff::class, 'sub_modul_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'modul_id', 'id');
    }

        public function modul()
    {
        return $this->belongsTo(ProjectModul::class, 'modul_id');
    }
    // App\Models\SubModul.php

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            SubModul::class,
            'sub_modul_dependencies',
            'sub_modul_id',
            'depends_on_sub_modul_id'
        )->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            SubModul::class,
            'sub_modul_dependencies',
            'depends_on_sub_modul_id',
            'sub_modul_id'
        )->withTimestamps();
    }

    public function scopeCriticalPath($query)
    {
        return $query->where('is_critical_path', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByModul($query, $modulId)
    {
        return $query->where('modul_id', $modulId);
    }

    // Accessors
    public function getPertInfoAttribute()
    {
        return [
            'est' => $this->est,
            'eft' => $this->eft,
            'lst' => $this->lst,
            'lft' => $this->lft,
            'total_float' => $this->total_float,
            'is_critical' => $this->is_critical_path,
        ];
    }

    // Helper methods
    public function isCriticalPath(): bool
    {
        return $this->is_critical_path;
    }

    public function hasFloat(): bool
    {
        return $this->total_float > 0;
    }

    public function getFloatDays(): float
    {
        return $this->total_float ?? 0;
    }




}
