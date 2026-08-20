<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::where('role', 'admin')->first()
            ?? User::factory()->create([
                'name' => 'Admin Test',
                'email' => 'admin_test@example.com',
                'role' => 'admin',
                'is_active' => true,
            ]);
    }

    public function test_can_fetch_all_settings(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'grouped',
                    'settings',
                    'system_info' => [
                        'php_version',
                        'laravel_version',
                        'environment',
                        'server_time',
                        'server_timezone',
                        'database_driver',
                    ],
                ],
            ]);

        $this->assertEquals('Apex Enterprise Solutions Pvt. Ltd.', $response->json('data.settings.company_name'));
    }

    public function test_can_update_settings_batch(): void
    {
        $payload = [
            'company_name' => 'Apex Global Enterprise Corp',
            'company_email' => 'contact@apexenterprise.com',
            'default_currency' => 'EUR',
            'currency_symbol' => '€',
            'default_tax_rate' => '12.50',
            'low_stock_threshold' => '30',
            'notify_on_deal_won' => '1',
            'allow_negative_stock' => '0',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'General settings saved and updated successfully.',
            ]);

        $this->assertEquals('Apex Global Enterprise Corp', Setting::get('company_name'));
        $this->assertEquals('EUR', Setting::get('default_currency'));
        $this->assertEquals('€', Setting::get('currency_symbol'));
        $this->assertEquals('12.50', Setting::get('default_tax_rate'));
        $this->assertEquals('30', Setting::get('low_stock_threshold'));
    }

    public function test_setting_validation_errors(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings', [
                'company_email' => 'not-a-valid-email',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The provided company email address is invalid.',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings', [
                'default_tax_rate' => 150, // exceeds 100%
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Default tax rate must be a percentage between 0 and 100.',
            ]);
    }

    public function test_can_reset_settings_to_defaults(): void
    {
        // Alter a setting first
        Setting::set('company_name', 'Temporary Name Inc.');
        $this->assertEquals('Temporary Name Inc.', Setting::get('company_name'));

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings/reset');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertEquals('Apex Enterprise Solutions Pvt. Ltd.', Setting::get('company_name'));
    }

    public function test_can_clear_system_cache(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings/cache-clear');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_web_dashboard_renders_with_settings(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/');

        $response->assertStatus(200)
            ->assertSee('General Settings')
            ->assertSee('tab-settings');
    }
}
