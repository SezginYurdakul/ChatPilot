<?php

namespace Tests\Unit\Services;

use App\Services\SettingsValidator;
use Tests\TestCase;

class SettingsValidatorTest extends TestCase
{
    public function test_rules_returns_settings_as_nullable_array(): void
    {
        $rules = SettingsValidator::rules();

        $this->assertArrayHasKey('settings', $rules);
        $this->assertEquals('nullable|array', $rules['settings']);
    }

    public function test_rules_generates_number_field_rules(): void
    {
        $rules = SettingsValidator::rules();

        $this->assertArrayHasKey('settings.rate_limit.cooldown_seconds', $rules);
        $this->assertStringContainsString('integer', $rules['settings.rate_limit.cooldown_seconds']);
        $this->assertStringContainsString('min:1', $rules['settings.rate_limit.cooldown_seconds']);
        $this->assertStringContainsString('max:60', $rules['settings.rate_limit.cooldown_seconds']);
    }

    public function test_rules_generates_select_field_rules(): void
    {
        $rules = SettingsValidator::rules();

        $this->assertArrayHasKey('settings.widget.theme', $rules);
        $this->assertStringContainsString('in:light,dark', $rules['settings.widget.theme']);
    }

    public function test_rules_generates_boolean_field_rules(): void
    {
        $rules = SettingsValidator::rules();

        $this->assertArrayHasKey('settings.ai.respond_when_offline', $rules);
        $this->assertStringContainsString('boolean', $rules['settings.ai.respond_when_offline']);
    }

    public function test_rules_generates_text_field_rules(): void
    {
        $rules = SettingsValidator::rules();

        $this->assertArrayHasKey('settings.widget.greeting', $rules);
        $this->assertStringContainsString('string', $rules['settings.widget.greeting']);
        $this->assertStringContainsString('max:1000', $rules['settings.widget.greeting']);
    }

    public function test_filter_unknown_keys_removes_unknown_groups(): void
    {
        $filtered = SettingsValidator::filterUnknownKeys([
            'widget' => ['theme' => 'dark'],
            'unknown_group' => ['foo' => 'bar'],
        ]);

        $this->assertArrayHasKey('widget', $filtered);
        $this->assertArrayNotHasKey('unknown_group', $filtered);
    }

    public function test_filter_unknown_keys_removes_unknown_fields(): void
    {
        $filtered = SettingsValidator::filterUnknownKeys([
            'widget' => [
                'theme' => 'dark',
                'nonexistent_field' => 'value',
            ],
        ]);

        $this->assertEquals('dark', $filtered['widget']['theme']);
        $this->assertArrayNotHasKey('nonexistent_field', $filtered['widget']);
    }

    public function test_filter_unknown_keys_handles_empty_input(): void
    {
        $filtered = SettingsValidator::filterUnknownKeys([]);

        $this->assertEquals([], $filtered);
    }

    public function test_filter_unknown_keys_skips_non_array_groups(): void
    {
        $filtered = SettingsValidator::filterUnknownKeys([
            'widget' => 'not-an-array',
        ]);

        $this->assertArrayNotHasKey('widget', $filtered);
    }
}
