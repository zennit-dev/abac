<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbacObjectAdditionalAttributesRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'key' => ['required', 'string'],
            'value' => ['required', 'string'],
            '_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                $object_table = config('abac.database.object_additional_attributes');

                if (!Schema::hasTable($object_table)) {
                    $fail("The object table '$object_table' doesn't exist.");
                }

                $object = DB::table($object_table)->where('id', $value)->first();

                if (is_null($object)) {
                    $fail('The subject_id is invalid.');
                }

                $softDeletesColumn = config('abac.database.soft_deletes_column');
                if ($softDeletesColumn && isset($object->{$softDeletesColumn})) {
                    $fail('The subject_id is invalid (soft deleted).');
                }
            }],
        ];
    }
}
