<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            [
                'name' => 'Premium Office Space - Victoria Island',
                'description' => 'Grade A office space in the heart of Victoria Island, Lagos',
                'type' => 'real_estate',
                'total_value' => 500000000, // 500M NGN
                'minimum_investment' => 100000, // 100K NGN
                'total_shares' => 5000,
                'available_shares' => 5000,
                'share_price' => 100000,
                'expected_roi' => 15.5,
                'risk_level' => 'medium',
                'status' => 'active',
                'metadata' => json_encode([
                    'location' => 'Victoria Island, Lagos',
                    'size' => '1000 sqm',
                    'year_built' => 2020,
                    'facilities' => [
                        'Parking Space',
                        '24/7 Power Supply',
                        'Security',
                        'High-speed Internet'
                    ]
                ])
            ],
            [
                'name' => 'FGN Savings Bond',
                'description' => 'Federal Government of Nigeria Savings Bond',
                'type' => 'stocks',
                'total_value' => 1000000000, // 1B NGN
                'minimum_investment' => 50000, // 50K NGN
                'total_shares' => 20000,
                'available_shares' => 20000,
                'share_price' => 50000,
                'expected_roi' => 11.382,
                'risk_level' => 'low',
                'status' => 'active',
                'metadata' => json_encode([
                    'tenor' => '3 years',
                    'interest_payment' => 'Quarterly',
                    'maturity_date' => '2027-01-10'
                ])
            ]
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }
    }
}