<?php

namespace Xoshbin\CustomFields\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Xoshbin\CustomFields\Traits\HasCustomFields;

/**
 * Test model for testing HasCustomFields trait functionality.
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Partner extends Model
{
    use HasCustomFields;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
