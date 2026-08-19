<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductAndWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Warehouses
        $warehouses = [
            [
                'name' => 'Silicon Valley Central Hub',
                'code' => 'WH-US-01',
                'street' => '100 Innovation Way',
                'city' => 'San Jose',
                'state' => 'CA',
                'country' => 'United States',
                'postal_code' => '95110',
                'is_active' => true,
            ],
            [
                'name' => 'New York Logistics Depot',
                'code' => 'WH-US-02',
                'street' => '450 Harbor Boulevard',
                'city' => 'Newark',
                'state' => 'NJ',
                'country' => 'United States',
                'postal_code' => '07102',
                'is_active' => true,
            ],
            [
                'name' => 'London Thames Logistics Hub',
                'code' => 'WH-EU-01',
                'street' => '12 Docklands Way',
                'city' => 'London',
                'country' => 'United Kingdom',
                'postal_code' => 'E14 2AA',
                'is_active' => true,
            ],
            [
                'name' => 'Singapore Changi Gateway',
                'code' => 'WH-APAC-01',
                'street' => '88 Changi South Ave 2',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'postal_code' => '486351',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::firstOrCreate(['code' => $wh['code']], $wh);
        }

        $allWarehouses = Warehouse::all();

        // 2. Products
        $products = [
            [
                'sku' => 'SW-ERP-ENT',
                'name' => 'Sales ERP Enterprise License (Annual)',
                'description' => 'Unlimited seats, dedicated compute cluster, 99.99% SLA, 24/7 priority support.',
                'category' => 'Software',
                'subcategory' => 'Enterprise Cloud',
                'type' => 'service',
                'unit_price' => 12000.00,
                'cost_price' => 2400.00,
                'tax_rate' => 10.00,
                'hsn_code' => '997331',
                'unit' => 'license',
                'min_stock_level' => 10,
                'reorder_point' => 20,
            ],
            [
                'sku' => 'SW-SEC-SHIELD',
                'name' => 'CloudShield Zero-Trust Security Suite',
                'description' => 'AI-driven threat detection, endpoint DLP, and SSO authentication.',
                'category' => 'Software',
                'subcategory' => 'Cybersecurity',
                'type' => 'service',
                'unit_price' => 4500.00,
                'cost_price' => 900.00,
                'tax_rate' => 10.00,
                'hsn_code' => '997331',
                'unit' => 'license',
                'min_stock_level' => 10,
                'reorder_point' => 20,
            ],
            [
                'sku' => 'HW-SRV-R900',
                'name' => 'Rackmount AI Server Ultra R900',
                'description' => 'Dual AMD EPYC 9654, 768GB DDR5 ECC, 4x NVIDIA L40S 48GB GPUs, 30TB NVMe U.2.',
                'category' => 'Hardware',
                'subcategory' => 'Servers & Infrastructure',
                'type' => 'product',
                'unit_price' => 28500.00,
                'cost_price' => 19500.00,
                'tax_rate' => 18.00,
                'hsn_code' => '847150',
                'unit' => 'unit',
                'min_stock_level' => 3,
                'reorder_point' => 8,
            ],
            [
                'sku' => 'HW-WRK-PRO16',
                'name' => 'Titan Pro Developer Workstation 16"',
                'description' => 'Intel Core Ultra 9, 64GB LPDDR5X, 2TB PCIe Gen5 SSD, OLED 120Hz Touch Display.',
                'category' => 'Hardware',
                'subcategory' => 'Workstations',
                'type' => 'product',
                'unit_price' => 3200.00,
                'cost_price' => 2100.00,
                'tax_rate' => 18.00,
                'hsn_code' => '847130',
                'unit' => 'unit',
                'min_stock_level' => 10,
                'reorder_point' => 25,
            ],
            [
                'sku' => 'HW-NET-SW48G',
                'name' => 'Nexus 48-Port 100GbE Enterprise Switch',
                'description' => 'Managed Layer 3 datacenter switch with ultra-low latency ASIC and redundant PSUs.',
                'category' => 'Hardware',
                'subcategory' => 'Networking',
                'type' => 'product',
                'unit_price' => 8900.00,
                'cost_price' => 5600.00,
                'tax_rate' => 18.00,
                'hsn_code' => '851762',
                'unit' => 'unit',
                'min_stock_level' => 5,
                'reorder_point' => 12,
            ],
            [
                'sku' => 'SV-IMPL-PACK',
                'name' => 'Enterprise Onboarding & Migration Service',
                'description' => 'Dedicated solutions architect, data migration, schema design, and custom integrations.',
                'category' => 'Services',
                'subcategory' => 'Professional Services',
                'type' => 'service',
                'unit_price' => 7500.00,
                'cost_price' => 3000.00,
                'tax_rate' => 10.00,
                'hsn_code' => '998313',
                'unit' => 'package',
                'min_stock_level' => 0,
                'reorder_point' => 0,
            ],
        ];

        foreach ($products as $pData) {
            $product = Product::firstOrCreate(['sku' => $pData['sku']], $pData);

            // Populate initial inventory in warehouses
            if ($product->type === 'product') {
                foreach ($allWarehouses as $index => $wh) {
                    $qty = ($index === 0) ? rand(25, 60) : rand(10, 30);
                    Inventory::firstOrCreate(
                        ['product_id' => $product->id, 'warehouse_id' => $wh->id],
                        [
                            'quantity' => $qty,
                            'reserved_quantity' => rand(0, 2),
                            'last_restocked_at' => now()->subDays(rand(1, 20)),
                        ]
                    );

                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $wh->id,
                        'type' => 'in',
                        'quantity' => $qty,
                        'notes' => 'Initial inventory restock',
                        'performed_by' => $admin ? $admin->id : null,
                        'created_at' => now()->subDays(20),
                    ]);
                }
            }
        }
    }
}
