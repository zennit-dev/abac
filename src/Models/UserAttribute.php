<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAttribute extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = [
            config('abac.tables.user_attributes.subject_type_column', 'subject_type'),
            config('abac.tables.user_attributes.subject_id_column', 'subject_id'),
            config('abac.tables.user_attributes.attribute_name_column', 'attribute_name'),
            config('abac.tables.user_attributes.attribute_value_column', 'attribute_value'),
        ];
    }

    public function getTable(): string
    {
        return config('abac.tables.user_attributes.name', parent::getTable());
    }

    public function subject(): MorphTo
    {
        return $this->morphTo(
            'subject',
            config('abac.tables.user_attributes.subject_type_column', 'subject_type'),
            config('abac.tables.user_attributes.subject_id_column', 'subject_id')
        );
    }

    public function getMorphType(): string
    {
        return config('abac.tables.user_attributes.subject_type_column', 'subject_type');
    }

    public  function getMorphID(): string
    {
        return config('abac.tables.user_attributes.subject_id_column', 'subject_id');
    }
}
