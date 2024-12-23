<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Traits\HasConfigurations;

class UserAttribute extends Model
{
    use HasConfigurations;

    protected $fillable = [
        'attribute_name',
        'attribute_value',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $userAttributeTable = $this->getUserAttributesTable();
        $this->fillable[] = $userAttributeTable['subject_type_column'];
        $this->fillable[] = $userAttributeTable['subject_id_column'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo(
            'subject',
            $this->getUserAttributesTable()['subject_type_column'],
            $this->getUserAttributesTable()['subject_id_column']
        );
    }
}
