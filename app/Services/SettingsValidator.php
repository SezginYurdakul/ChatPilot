<?php

namespace App\Services;

use Illuminate\Support\Arr;

class SettingsValidator
{
    /**
     * Build Laravel validation rules dynamically from the settings schema.
     *
     * Reads config('chatpilot.settings_schema') and converts each field
     * definition into a Laravel validation rule string.
     *
     * Example output:
     *   [
     *     'settings'                                => 'nullable|array',
     *     'settings.rate_limit.cooldown_seconds'     => 'sometimes|integer|min:1|max:60',
     *     'settings.widget.theme'                    => 'sometimes|in:light,dark',
     *     'settings.ai.respond_when_offline'         => 'sometimes|boolean',
     *   ]
     *
     * @return array<string, string>
     */
    public static function rules(): array
    {
        $schema = config('chatpilot.settings_schema', []);
        $rules = ['settings' => 'nullable|array'];

        foreach ($schema as $group => $groupDef) {
            foreach ($groupDef['fields'] ?? [] as $field => $fieldDef) {
                $key = "settings.{$group}.{$field}";
                $rules[$key] = self::buildFieldRule($fieldDef);
            }
        }

        return $rules;
    }

    /**
     * Strip any keys from the input settings that are not defined in the schema.
     *
     * Prevents unknown/garbage keys from polluting the settings JSON.
     * Only top-level groups and their declared fields are allowed through.
     *
     * @param  array  $input  Raw settings array from the request
     * @return array  Filtered settings containing only schema-defined keys
     */
    public static function filterUnknownKeys(array $input): array
    {
        $schema = config('chatpilot.settings_schema', []);
        $filtered = [];

        foreach ($input as $group => $fields) {
            // Reject groups not defined in schema
            if (! isset($schema[$group])) {
                continue;
            }

            if (! is_array($fields)) {
                continue;
            }

            $allowedFields = array_keys($schema[$group]['fields'] ?? []);
            $filtered[$group] = Arr::only($fields, $allowedFields);
        }

        return $filtered;
    }

    /**
     * Convert a single field definition into a Laravel validation rule string.
     *
     * Supports types: number, text, boolean, select.
     */
    private static function buildFieldRule(array $fieldDef): string
    {
        $parts = ['sometimes'];

        switch ($fieldDef['type']) {
            case 'number':
                $parts[] = 'integer';
                if (isset($fieldDef['min'])) {
                    $parts[] = "min:{$fieldDef['min']}";
                }
                if (isset($fieldDef['max'])) {
                    $parts[] = "max:{$fieldDef['max']}";
                }
                break;

            case 'text':
                $parts[] = 'string';
                $parts[] = 'max:1000';
                break;

            case 'boolean':
                $parts[] = 'boolean';
                break;

            case 'select':
                if (isset($fieldDef['options'])) {
                    $parts[] = 'in:' . implode(',', $fieldDef['options']);
                }
                break;
        }

        return implode('|', $parts);
    }
}
