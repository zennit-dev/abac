<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbacSubjectAdditionalAttributeRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'subject' => [
                'required',
                'string',
                'regex:/^App\\\\Models(\\\\[A-Z][A-Za-z0-9_]*)+$/',
            ],
            'subject_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                $subject_table = $this->string('subject');

                if (!Schema::hasTable($subject_table)) {
                    $fail("The subject table '$subject_table' doesn't exist.");
                }

                $subject = DB::table($subject_table)->where('id', $value)->first();
                if (is_null($subject)) {
                    $fail('The subject_id is invalid.');
                }

                $softDeletesColumn = config('abac.database.soft_deletes_column');
                if ($softDeletesColumn && isset($subject->{$softDeletesColumn})) {
                    $fail('The subject_id is invalid (soft deleted).');
                }
            }],
            'attribute_name' => ['required', 'string'],
            'attribute_value' => ['required', 'string'],
        ];
    }
}
