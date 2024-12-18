<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Services\ConfigurationService;

class UserAttribute extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $config = app(ConfigurationService::class);
        $tableConfig = $config->getUserAttributesTable();

        $this->table = $tableConfig['name'];
        $this->fillable = [
            $tableConfig['subject_type_column'],
            $tableConfig['subject_id_column'],
            $tableConfig['attribute_name_column'],
            $tableConfig['attribute_value_column'],
        ];
    }

    public function subject(): MorphTo
    {
        $config = app(ConfigurationService::class);

        return $this->morphTo(
            'subject',
            $config->getUserAttributesTable()['subject_type_column'],
            $config->getUserAttributesTable()['subject_id_column']
        );
    }
}
