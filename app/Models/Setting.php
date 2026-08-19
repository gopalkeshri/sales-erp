<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Cache key for all settings
     */
    public const CACHE_KEY = 'sales_erp_settings_all';

    /**
     * Default enterprise settings map
     */
    public static function defaultSettings(): array
    {
        return [
            // Company Profile
            [
                'key' => 'company_name',
                'value' => 'Global B2B Solutions Inc.',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Registered business entity name',
            ],
            [
                'key' => 'company_tagline',
                'value' => 'Enterprise B2B Revenue & Fulfillment Platform',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Organization slogan / sub-heading',
            ],
            [
                'key' => 'company_email',
                'value' => 'admin@saleserp.enterprise',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Primary corporate contact email',
            ],
            [
                'key' => 'company_phone',
                'value' => '+1 (800) 555-0199',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Official customer care & support phone',
            ],
            [
                'key' => 'company_website',
                'value' => 'https://saleserp.enterprise',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Official website URL',
            ],
            [
                'key' => 'tax_id',
                'value' => 'US-TAX-88902148',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Tax Identification / GSTIN / VAT registration number',
            ],
            [
                'key' => 'company_address',
                'value' => '100 Enterprise Way, Suite 400',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Street address',
            ],
            [
                'key' => 'company_city',
                'value' => 'San Francisco',
                'group' => 'company',
                'type' => 'string',
                'description' => 'City',
            ],
            [
                'key' => 'company_state',
                'value' => 'California',
                'group' => 'company',
                'type' => 'string',
                'description' => 'State / Province',
            ],
            [
                'key' => 'company_postal_code',
                'value' => '94105',
                'group' => 'company',
                'type' => 'string',
                'description' => 'ZIP / Postal Code',
            ],
            [
                'key' => 'company_country',
                'value' => 'United States',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Country',
            ],

            // Localization
            [
                'key' => 'default_currency',
                'value' => 'USD',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Base currency code (e.g. USD, EUR, GBP, INR)',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Currency display symbol (e.g. $, €, £, ₹)',
            ],
            [
                'key' => 'currency_position',
                'value' => 'prefix',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Currency symbol placement (prefix or suffix)',
            ],
            [
                'key' => 'timezone',
                'value' => 'America/New_York',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Default system timezone',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Standard date format display',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Standard time format display (24h or 12h)',
            ],
            [
                'key' => 'financial_year_start',
                'value' => '01-01',
                'group' => 'localization',
                'type' => 'string',
                'description' => 'Financial Year commencement date (MM-DD)',
            ],

            // Sales & Invoicing
            [
                'key' => 'quote_prefix',
                'value' => 'QT-',
                'group' => 'sales',
                'type' => 'string',
                'description' => 'Quotation reference number prefix',
            ],
            [
                'key' => 'order_prefix',
                'value' => 'SO-',
                'group' => 'sales',
                'type' => 'string',
                'description' => 'Sales Order reference number prefix',
            ],
            [
                'key' => 'invoice_prefix',
                'value' => 'INV-',
                'group' => 'sales',
                'type' => 'string',
                'description' => 'Tax Invoice reference number prefix',
            ],
            [
                'key' => 'default_tax_rate',
                'value' => '10.00',
                'group' => 'sales',
                'type' => 'number',
                'description' => 'Default standard sales tax rate percentage',
            ],
            [
                'key' => 'default_payment_terms',
                'value' => 'net_30',
                'group' => 'sales',
                'type' => 'string',
                'description' => 'Standard customer payment terms',
            ],
            [
                'key' => 'default_commission_rate',
                'value' => '5.00',
                'group' => 'sales',
                'type' => 'number',
                'description' => 'Base commission rate for sales representatives',
            ],
            [
                'key' => 'auto_generate_invoice',
                'value' => '1',
                'group' => 'sales',
                'type' => 'boolean',
                'description' => 'Automatically create draft invoice upon order confirmation',
            ],

            // Inventory
            [
                'key' => 'low_stock_threshold',
                'value' => '20',
                'group' => 'inventory',
                'type' => 'number',
                'description' => 'Global threshold to trigger low stock inventory alerts',
            ],
            [
                'key' => 'allow_negative_stock',
                'value' => '0',
                'group' => 'inventory',
                'type' => 'boolean',
                'description' => 'Allow sales dispatch when warehouse inventory is zero',
            ],
            [
                'key' => 'stock_valuation_method',
                'value' => 'FIFO',
                'group' => 'inventory',
                'type' => 'string',
                'description' => 'Inventory valuation costing standard (FIFO / LIFO / Weighted Average)',
            ],

            // Notifications & Alerts
            [
                'key' => 'enable_email_notifications',
                'value' => '1',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Master toggle for system email notifications',
            ],
            [
                'key' => 'notify_on_new_lead',
                'value' => '1',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send alert when a new lead is captured or assigned',
            ],
            [
                'key' => 'notify_on_deal_won',
                'value' => '1',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send celebration email when an opportunity is closed won',
            ],
            [
                'key' => 'notify_on_order_placed',
                'value' => '1',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Notify fulfillment and billing teams on order submission',
            ],
            [
                'key' => 'notify_on_payment_received',
                'value' => '1',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Send receipt alert when customer payment is recorded',
            ],
            [
                'key' => 'notify_on_low_stock',
                'value' => '1',
                'group' => 'notifications',
                'type' => 'boolean',
                'description' => 'Notify operations manager when SKU inventory dips below minimum threshold',
            ],
            [
                'key' => 'admin_alert_email',
                'value' => 'alerts@saleserp.enterprise',
                'group' => 'notifications',
                'type' => 'string',
                'description' => 'Recipient email for critical system alerts and digests',
            ],
        ];
    }

    /**
     * Get a setting by key with optional default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::getAllKeyValue();
        return $all[$key] ?? $default;
    }

    /**
     * Set / Update a single setting
     */
    public static function set(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'string',
        ?string $description = null
    ): static {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );

        Cache::forget(static::CACHE_KEY);
        return $setting;
    }

    /**
     * Batch save settings
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $existing = static::where('key', $key)->first();
            if ($existing) {
                $existing->value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
                $existing->save();
            } else {
                static::create([
                    'key' => $key,
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'group' => 'general',
                    'type' => is_numeric($value) ? 'number' : (is_bool($value) ? 'boolean' : 'string'),
                ]);
            }
        }

        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Get all settings as a key => value array with caching
     */
    public static function getAllKeyValue(): array
    {
        return Cache::remember(static::CACHE_KEY, 3600, function () {
            if (static::count() === 0) {
                static::seedDefaults();
            }

            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped(): array
    {
        if (static::count() === 0) {
            static::seedDefaults();
        }

        return static::all()->groupBy('group')->toArray();
    }

    /**
     * Populate default settings
     */
    public static function seedDefaults(): void
    {
        $defaults = static::defaultSettings();
        foreach ($defaults as $def) {
            static::updateOrCreate(
                ['key' => $def['key']],
                $def
            );
        }

        Cache::forget(static::CACHE_KEY);
    }
}
