<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'faq_tag_id',
        'question',
        'answer'
    ];


    public function faqTag(): BelongsTo
    {
        return $this->BelongsTo(FaqTag::class);
    }
}