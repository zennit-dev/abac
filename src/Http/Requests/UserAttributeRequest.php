<?php

namespace zennit\ABAC\Http\Requests;

use Illuminate\Support\Facades\DB;
use zennit\ABAC\Http\Requests\Core\Request;
use zennit\ABAC\Traits\AbacHasConfigurations;

class UserAttributeRequest extends Request
{
    use AbacHasConfigurations;

    protected function getRules(): array
    {
        return [
            'subject_type' => ['required', 'string'],
            'subject_id' => ['required', 'string', function ($attribute, $value, $fail) {
                $user_table = DB::table($this->getUserAttributeSubjectType());
                $user = $user_table->where('id', $value)->first();

                if (is_null($user)) {
                    $fail('The subject_id is invalid.');
                }

                $softDeletesColumn = config('abac.database.user_soft_deletes_column');
                if ($user->{$softDeletesColumn} !== null) {
                    $fail('The subject_id is invalid.');
                }

                return true;
            }],
        ];
    }
}
