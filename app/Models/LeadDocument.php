<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDocument extends Model
{
    public const LABELS = [
        'ID' => 'Owner ID',
        'VC' => 'Voided check / bank letter',
        'TAX_ID' => 'Tax ID',
        'Projections' => '1st Year Projections',
        'Pics' => 'POS pictures',
        'supportingdoc' => 'Supporting documents',
        'bank' => 'Bank statement',
        'ccp' => 'Processing statement',
        'pos' => 'POS statement',
    ];

    protected $fillable = ['lead_id', 'category', 'file_name', 's3_key', 'attach_status', 'attach_error', 'attached_at'];

    protected function casts(): array
    {
        return [
            'attached_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function label(): string
    {
        return self::LABELS[$this->category] ?? $this->category;
    }
}
