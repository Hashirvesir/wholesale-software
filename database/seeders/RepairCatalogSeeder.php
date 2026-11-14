<?php

namespace Database\Seeders;

use App\Models\RepairBrand;
use App\Models\RepairModel;
use App\Models\RepairService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RepairCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. SERVICES & FAULTS SEEDING
        $services = [
            // Screen & Glass
            ['category' => 'Screen & Glass', 'name' => 'Front Glass Broken / Touch Replacement', 'default_labor_cost' => 800],
            ['category' => 'Screen & Glass', 'name' => 'Back Glass Broken', 'default_labor_cost' => 900],
            ['category' => 'Screen & Glass', 'name' => 'Complete LCD / Panel Replacement', 'default_labor_cost' => 600],
            ['category' => 'Screen & Glass', 'name' => 'LCD Ribbon / Flex Bonding Issue', 'default_labor_cost' => 1500],
            ['category' => 'Screen & Glass', 'name' => 'Touch Not Working / Ghost Touch', 'default_labor_cost' => 800],

            // Charging & Battery
            ['category' => 'Charging & Battery', 'name' => 'Charging Port / Flex Cable Issue', 'default_labor_cost' => 500],
            ['category' => 'Charging & Battery', 'name' => 'Battery Replacement / Health Down', 'default_labor_cost' => 400],
            ['category' => 'Charging & Battery', 'name' => 'Slow Charging / Not Charging IC Issue', 'default_labor_cost' => 1200],

            // Audio & Calling
            ['category' => 'Audio & Calling', 'name' => 'Ear Speaker / Calling Voice Low', 'default_labor_cost' => 400],
            ['category' => 'Audio & Calling', 'name' => 'Loudspeaker / Ringer Fault', 'default_labor_cost' => 400],
            ['category' => 'Audio & Calling', 'name' => 'Mic / Voice Sending Fault', 'default_labor_cost' => 400],

            // Camera
            ['category' => 'Camera', 'name' => 'Back Camera Fault / Shaking', 'default_labor_cost' => 800],
            ['category' => 'Camera', 'name' => 'Front Camera Fault', 'default_labor_cost' => 700],
            ['category' => 'Camera', 'name' => 'Camera Glass Lens Broken', 'default_labor_cost' => 400],

            // Board & Hardware
            ['category' => 'Board & Hardware', 'name' => 'Water Damaged / Shorting Cleaning', 'default_labor_cost' => 1500],
            ['category' => 'Board & Hardware', 'name' => 'Dead Phone / Power IC Motherboard Repair', 'default_labor_cost' => 2000],
            ['category' => 'Board & Hardware', 'name' => 'Network / No Service / Baseband Fault', 'default_labor_cost' => 1500],
            ['category' => 'Board & Hardware', 'name' => 'Face ID / Fingerprint Sensor Issue', 'default_labor_cost' => 1500],
            ['category' => 'Board & Hardware', 'name' => 'Housing / Body Frame Change', 'default_labor_cost' => 800],

            // Software & Flashing
            ['category' => 'Software & Flashing', 'name' => 'Software / Flashing / Logo Stuck', 'default_labor_cost' => 700],
            ['category' => 'Software & Flashing', 'name' => 'FRP Lock / Google Account Bypass', 'default_labor_cost' => 1000],
            ['category' => 'Software & Flashing', 'name' => 'Restarting / Bootloop Issue', 'default_labor_cost' => 800],
        ];

        foreach ($services as $order => $srv) {
            RepairService::updateOrCreate(
                ['name' => $srv['name']],
                [
                    'category' => $srv['category'],
                    'default_labor_cost' => $srv['default_labor_cost'],
                    'sort_order' => $order + 1,
                    'status' => true,
                ]
            );
        }

        // 2. BRANDS & MODELS SEEDING
        $brandsData = [
            'Apple' => [
                'iPhone 6 Series' => ['iPhone 6', 'iPhone 6 Plus', 'iPhone 6s', 'iPhone 6s Plus'],
                'iPhone 7 & 8 Series' => ['iPhone 7', 'iPhone 7 Plus', 'iPhone 8', 'iPhone 8 Plus', 'iPhone SE (1st Gen)', 'iPhone SE (2nd Gen)', 'iPhone SE (3rd Gen)'],
                'iPhone X Series' => ['iPhone X', 'iPhone XR', 'iPhone XS', 'iPhone XS Max'],
                'iPhone 11 Series' => ['iPhone 11', 'iPhone 11 Pro', 'iPhone 11 Pro Max'],
                'iPhone 12 Series' => ['iPhone 12', 'iPhone 12 Mini', 'iPhone 12 Pro', 'iPhone 12 Pro Max'],
                'iPhone 13 Series' => ['iPhone 13', 'iPhone 13 Mini', 'iPhone 13 Pro', 'iPhone 13 Pro Max'],
                'iPhone 14 Series' => ['iPhone 14', 'iPhone 14 Plus', 'iPhone 14 Pro', 'iPhone 14 Pro Max'],
                'iPhone 15 Series' => ['iPhone 15', 'iPhone 15 Plus', 'iPhone 15 Pro', 'iPhone 15 Pro Max'],
                'iPhone 16 Series' => ['iPhone 16', 'iPhone 16 Plus', 'iPhone 16 Pro', 'iPhone 16 Pro Max'],
                'iPhone 17 & 18 Series' => ['iPhone 17', 'iPhone 17 Air', 'iPhone 17 Pro', 'iPhone 17 Pro Max', 'iPhone 18', 'iPhone 18 Pro', 'iPhone 18 Pro Max'],
                'iPads & Tablets' => [
                    'iPad Mini 4', 'iPad Mini 5', 'iPad Mini 6',
                    'iPad Air 3', 'iPad Air 4', 'iPad Air 5', 'iPad Air 11" (M2)', 'iPad Air 13" (M2)',
                    'iPad 7th Gen (10.2")', 'iPad 8th Gen (10.2")', 'iPad 9th Gen (10.2")', 'iPad 10th Gen (10.9")',
                    'iPad Pro 11" (1st Gen)', 'iPad Pro 11" (2nd Gen)', 'iPad Pro 11" (M1)', 'iPad Pro 11" (M2)', 'iPad Pro 11" (M4)',
                    'iPad Pro 12.9" (3rd Gen)', 'iPad Pro 12.9" (4th Gen)', 'iPad Pro 12.9" (M1)', 'iPad Pro 12.9" (M2)', 'iPad Pro 13" (M4)'
                ]
            ],
            'Samsung' => [
                'Galaxy S Series' => [
                    'Galaxy S6', 'Galaxy S6 Edge', 'Galaxy S7', 'Galaxy S7 Edge',
                    'Galaxy S8', 'Galaxy S8+', 'Galaxy S9', 'Galaxy S9+',
                    'Galaxy S10e', 'Galaxy S10', 'Galaxy S10+',
                    'Galaxy S20', 'Galaxy S20+', 'Galaxy S20 Ultra', 'Galaxy S20 FE',
                    'Galaxy S21', 'Galaxy S21+', 'Galaxy S21 Ultra', 'Galaxy S21 FE',
                    'Galaxy S22', 'Galaxy S22+', 'Galaxy S22 Ultra',
                    'Galaxy S23', 'Galaxy S23+', 'Galaxy S23 Ultra', 'Galaxy S23 FE',
                    'Galaxy S24', 'Galaxy S24+', 'Galaxy S24 Ultra', 'Galaxy S24 FE',
                    'Galaxy S25', 'Galaxy S25+', 'Galaxy S25 Ultra', 'Galaxy S25 Slim'
                ],
                'Galaxy Note Series' => [
                    'Galaxy Note 5', 'Galaxy Note 8', 'Galaxy Note 9',
                    'Galaxy Note 10', 'Galaxy Note 10+', 'Galaxy Note 10 Lite',
                    'Galaxy Note 20', 'Galaxy Note 20 Ultra'
                ],
                'Galaxy Z Fold & Flip' => [
                    'Galaxy Fold', 'Galaxy Z Fold 2', 'Galaxy Z Fold 3', 'Galaxy Z Fold 4', 'Galaxy Z Fold 5', 'Galaxy Z Fold 6',
                    'Galaxy Z Flip', 'Galaxy Z Flip 3', 'Galaxy Z Flip 4', 'Galaxy Z Flip 5', 'Galaxy Z Flip 6'
                ],
                'Galaxy A Series' => [
                    'Galaxy A01', 'Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06',
                    'Galaxy A10', 'Galaxy A10s', 'Galaxy A11', 'Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16',
                    'Galaxy A20', 'Galaxy A20s', 'Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25',
                    'Galaxy A30', 'Galaxy A30s', 'Galaxy A31', 'Galaxy A32', 'Galaxy A33 5G', 'Galaxy A34 5G', 'Galaxy A35 5G',
                    'Galaxy A50', 'Galaxy A50s', 'Galaxy A51', 'Galaxy A52', 'Galaxy A52s 5G', 'Galaxy A53 5G', 'Galaxy A54 5G', 'Galaxy A55 5G',
                    'Galaxy A70', 'Galaxy A71', 'Galaxy A72', 'Galaxy A73 5G'
                ],
                'Galaxy M & F Series' => [
                    'Galaxy M11', 'Galaxy M12', 'Galaxy M21', 'Galaxy M31', 'Galaxy M32', 'Galaxy M51', 'Galaxy M52',
                    'Galaxy F12', 'Galaxy F22', 'Galaxy F23', 'Galaxy F54'
                ],
                'Galaxy Tablets' => [
                    'Galaxy Tab A7', 'Galaxy Tab A7 Lite', 'Galaxy Tab A8', 'Galaxy Tab A9', 'Galaxy Tab A9+',
                    'Galaxy Tab S6 Lite', 'Galaxy Tab S7', 'Galaxy Tab S8', 'Galaxy Tab S9', 'Galaxy Tab S9 FE'
                ]
            ],
            'Oppo' => [
                'Reno Series' => [
                    'Reno 2', 'Reno 2F', 'Reno 3', 'Reno 3 Pro', 'Reno 4', 'Reno 4 Pro',
                    'Reno 5', 'Reno 5 Pro', 'Reno 6', 'Reno 6 Pro', 'Reno 7', 'Reno 7 Pro',
                    'Reno 8', 'Reno 8 Pro', 'Reno 9', 'Reno 10', 'Reno 10 Pro', 'Reno 11', 'Reno 11 Pro',
                    'Reno 12', 'Reno 12 Pro', 'Reno 12F'
                ],
                'F Series' => [
                    'F7', 'F9', 'F9 Pro', 'F11', 'F11 Pro', 'F15', 'F17', 'F17 Pro',
                    'F19', 'F19 Pro', 'F19 Pro+', 'F21 Pro', 'F21 Pro 5G', 'F23 5G', 'F25 Pro 5G', 'F27 Pro+'
                ],
                'A Series' => [
                    'A1k', 'A3s', 'A5s', 'A7', 'A9', 'A12', 'A15', 'A15s', 'A16', 'A16e', 'A17', 'A17k',
                    'A18', 'A38', 'A53', 'A54', 'A57 (4G)', 'A58', 'A76', 'A77', 'A77s', 'A78', 'A79 5G', 'A96', 'A98 5G'
                ],
                'Find Series' => [
                    'Find X2', 'Find X3 Pro', 'Find X5 Pro', 'Find X6 Pro', 'Find X7 Ultra',
                    'Find N2 Flip', 'Find N3 Flip', 'Find N3 Fold'
                ]
            ],
            'Vivo' => [
                'V Series' => [
                    'V11', 'V11 Pro', 'V15', 'V15 Pro', 'V17', 'V17 Pro', 'V19',
                    'V20', 'V20 SE', 'V21', 'V21e', 'V23 5G', 'V23e', 'V25 5G', 'V25e',
                    'V27 5G', 'V27e', 'V29 5G', 'V29e', 'V30 5G', 'V30e', 'V30 Lite', 'V40 5G', 'V40e', 'V40 Lite'
                ],
                'Y Series' => [
                    'Y02', 'Y02s', 'Y03', 'Y11', 'Y12', 'Y12s', 'Y15', 'Y15s', 'Y17',
                    'Y20', 'Y20s', 'Y21', 'Y21T', 'Y22', 'Y27', 'Y28', 'Y33s', 'Y35', 'Y36',
                    'Y51', 'Y53s', 'Y100', 'Y200'
                ],
                'X Series & Fold' => [
                    'X50', 'X60 Pro', 'X70 Pro', 'X80', 'X80 Pro', 'X90', 'X90 Pro',
                    'X100', 'X100 Pro', 'X100 Ultra', 'X Fold', 'X Fold 2', 'X Fold 3 Pro'
                ],
                'iQOO Series' => [
                    'iQOO 7', 'iQOO 9 Pro', 'iQOO 11', 'iQOO 12', 'iQOO 13', 'iQOO Neo 7', 'iQOO Neo 9 Pro', 'iQOO Z7', 'iQOO Z9'
                ]
            ],
            'Infinix' => [
                'Hot Series' => [
                    'Hot 8', 'Hot 9', 'Hot 9 Play', 'Hot 10', 'Hot 10 Play', 'Hot 10S',
                    'Hot 11', 'Hot 11 Play', 'Hot 11S', 'Hot 12', 'Hot 12 Play', 'Hot 12i',
                    'Hot 20', 'Hot 20 Play', 'Hot 20i', 'Hot 30', 'Hot 30i', 'Hot 30 Play',
                    'Hot 40', 'Hot 40i', 'Hot 40 Pro', 'Hot 50', 'Hot 50i', 'Hot 50 Pro', 'Hot 50 Pro+'
                ],
                'Note Series' => [
                    'Note 7', 'Note 7 Lite', 'Note 8', 'Note 8i', 'Note 10', 'Note 10 Pro',
                    'Note 11', 'Note 11 Pro', 'Note 12', 'Note 12 G96', 'Note 12 VIP',
                    'Note 30', 'Note 30 Pro', 'Note 30 VIP', 'Note 40', 'Note 40 Pro', 'Note 40 Pro+ 5G'
                ],
                'Smart Series' => [
                    'Smart 4', 'Smart 5', 'Smart 6', 'Smart 6 Plus', 'Smart 7', 'Smart 7 HD', 'Smart 8', 'Smart 8 Pro', 'Smart 8 Plus', 'Smart 9'
                ],
                'Zero Series' => [
                    'Zero 8', 'Zero X', 'Zero X Pro', 'Zero 5G', 'Zero 20', 'Zero 30 4G', 'Zero 30 5G', 'Zero 40 4G', 'Zero 40 5G', 'Zero Flip'
                ],
                'GT Series' => [
                    'GT 10 Pro', 'GT 20 Pro'
                ]
            ],
            'Tecno' => [
                'Spark Series' => [
                    'Spark 4', 'Spark 4 Air', 'Spark 5', 'Spark 5 Air', 'Spark 6', 'Spark 6 Go',
                    'Spark 7', 'Spark 7T', 'Spark 7 Pro', 'Spark 8', 'Spark 8C', 'Spark 8 Pro',
                    'Spark 9', 'Spark 9T', 'Spark 9 Pro', 'Spark 10', 'Spark 10C', 'Spark 10 Pro',
                    'Spark 20', 'Spark 20C', 'Spark 20 Pro', 'Spark 20 Pro+', 'Spark 30', 'Spark 30 Pro'
                ],
                'Camon Series' => [
                    'Camon 15', 'Camon 15 Pro', 'Camon 16', 'Camon 16 Premier', 'Camon 17', 'Camon 17 Pro',
                    'Camon 18', 'Camon 18 Premier', 'Camon 19', 'Camon 19 Pro', 'Camon 20', 'Camon 20 Pro',
                    'Camon 30', 'Camon 30 5G', 'Camon 30 Pro', 'Camon 30 Premier'
                ],
                'Pova Series' => [
                    'Pova', 'Pova 2', 'Pova 3', 'Pova 4', 'Pova 4 Pro', 'Pova 5', 'Pova 5 Pro', 'Pova 6', 'Pova 6 Pro', 'Pova 6 Neo'
                ],
                'Pop Series' => [
                    'Pop 4', 'Pop 5', 'Pop 5 LTE', 'Pop 6', 'Pop 6 Pro', 'Pop 7', 'Pop 7 Pro', 'Pop 8', 'Pop 9'
                ],
                'Phantom Series' => [
                    'Phantom X', 'Phantom X2', 'Phantom X2 Pro', 'Phantom V Fold', 'Phantom V Flip'
                ]
            ],
            'Xiaomi / Redmi' => [
                'Redmi Note Series' => [
                    'Redmi Note 7', 'Redmi Note 8', 'Redmi Note 8 Pro', 'Redmi Note 9', 'Redmi Note 9 Pro', 'Redmi Note 9S',
                    'Redmi Note 10', 'Redmi Note 10 Pro', 'Redmi Note 10S', 'Redmi Note 11', 'Redmi Note 11 Pro', 'Redmi Note 11S',
                    'Redmi Note 12', 'Redmi Note 12 Pro', 'Redmi Note 12 Pro+', 'Redmi Note 13', 'Redmi Note 13 Pro', 'Redmi Note 13 Pro+',
                    'Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 14 Pro+'
                ],
                'Redmi Number Series' => [
                    'Redmi 7', 'Redmi 7A', 'Redmi 8', 'Redmi 8A', 'Redmi 9', 'Redmi 9A', 'Redmi 9C', 'Redmi 9T',
                    'Redmi 10', 'Redmi 10A', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13', 'Redmi 13C', 'Redmi 14C'
                ],
                'Xiaomi Flagship Series' => [
                    'Mi 9', 'Mi 9T', 'Mi 10', 'Mi 10T Pro', 'Mi 11', 'Mi 11 Ultra',
                    'Xiaomi 12', 'Xiaomi 12 Pro', 'Xiaomi 13', 'Xiaomi 13 Pro', 'Xiaomi 13 Ultra',
                    'Xiaomi 14', 'Xiaomi 14 Pro', 'Xiaomi 14 Ultra', 'Xiaomi 15', 'Xiaomi 15 Pro'
                ],
                'Poco Series' => [
                    'Poco C31', 'Poco C40', 'Poco C55', 'Poco C65',
                    'Poco M3', 'Poco M3 Pro', 'Poco M4 Pro', 'Poco M5', 'Poco M6 Pro',
                    'Poco X3 NFC', 'Poco X3 Pro', 'Poco X4 Pro 5G', 'Poco X5 Pro 5G', 'Poco X6', 'Poco X6 Pro',
                    'Poco F3', 'Poco F4', 'Poco F4 GT', 'Poco F5', 'Poco F5 Pro', 'Poco F6', 'Poco F6 Pro'
                ]
            ],
            'Realme' => [
                'Number Series' => [
                    'Realme 5', 'Realme 5 Pro', 'Realme 6', 'Realme 6 Pro', 'Realme 7', 'Realme 7 Pro',
                    'Realme 8', 'Realme 8 Pro', 'Realme 9', 'Realme 9 Pro', 'Realme 9 Pro+',
                    'Realme 10', 'Realme 10 Pro', 'Realme 10 Pro+', 'Realme 11', 'Realme 11 Pro', 'Realme 11 Pro+',
                    'Realme 12', 'Realme 12 Pro', 'Realme 12 Pro+', 'Realme 13', 'Realme 13 Pro+'
                ],
                'C Series' => [
                    'Realme C11', 'Realme C12', 'Realme C15', 'Realme C21', 'Realme C21Y', 'Realme C25', 'Realme C25s',
                    'Realme C30', 'Realme C31', 'Realme C33', 'Realme C35', 'Realme C51', 'Realme C53', 'Realme C55', 'Realme C67'
                ],
                'GT & Narzo Series' => [
                    'Realme GT', 'Realme GT Master Edition', 'Realme GT Neo 2', 'Realme GT Neo 3', 'Realme GT 2 Pro', 'Realme GT 3', 'Realme GT 5', 'Realme GT 6',
                    'Narzo 20', 'Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70 Pro'
                ]
            ],
            'OnePlus' => [
                'Flagship Series' => [
                    'OnePlus 6', 'OnePlus 6T', 'OnePlus 7', 'OnePlus 7 Pro', 'OnePlus 7T', 'OnePlus 7T Pro',
                    'OnePlus 8', 'OnePlus 8 Pro', 'OnePlus 8T', 'OnePlus 9', 'OnePlus 9 Pro', 'OnePlus 9R',
                    'OnePlus 10 Pro', 'OnePlus 10R', 'OnePlus 10T', 'OnePlus 11', 'OnePlus 11R',
                    'OnePlus 12', 'OnePlus 12R', 'OnePlus 13', 'OnePlus Open'
                ],
                'Nord Series' => [
                    'OnePlus Nord', 'OnePlus Nord CE', 'OnePlus Nord CE 2', 'OnePlus Nord 2',
                    'OnePlus Nord CE 3', 'OnePlus Nord 3', 'OnePlus Nord 4', 'OnePlus Nord CE 4 Lite'
                ]
            ],
            'Google Pixel' => [
                'Pixel Series' => [
                    'Pixel 3', 'Pixel 3 XL', 'Pixel 3a', 'Pixel 3a XL',
                    'Pixel 4', 'Pixel 4 XL', 'Pixel 4a', 'Pixel 4a (5G)',
                    'Pixel 5', 'Pixel 5a',
                    'Pixel 6', 'Pixel 6 Pro', 'Pixel 6a',
                    'Pixel 7', 'Pixel 7 Pro', 'Pixel 7a',
                    'Pixel 8', 'Pixel 8 Pro', 'Pixel 8a',
                    'Pixel 9', 'Pixel 9 Pro', 'Pixel 9 Pro XL', 'Pixel 9 Pro Fold'
                ]
            ],
            'Huawei' => [
                'Y & Nova Series' => [
                    'Huawei Y6p', 'Huawei Y7p', 'Huawei Y7 Prime', 'Huawei Y9 Prime', 'Huawei Y9a',
                    'Nova 3i', 'Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10', 'Nova 11', 'Nova 12'
                ],
                'P & Mate Series' => [
                    'Huawei P20 Pro', 'Huawei P30', 'Huawei P30 Pro', 'Huawei P40 Pro', 'Huawei P50 Pro', 'Huawei P60 Pro', 'Pura 70', 'Pura 70 Pro', 'Pura 70 Ultra',
                    'Mate 20 Pro', 'Mate 30 Pro', 'Mate 40 Pro', 'Mate 50 Pro', 'Mate 60 Pro'
                ]
            ],
        ];

        DB::transaction(function () use ($brandsData) {
            $brandOrder = 1;
            foreach ($brandsData as $brandName => $seriesGroups) {
                $brand = RepairBrand::updateOrCreate(
                    ['name' => $brandName],
                    ['sort_order' => $brandOrder++, 'status' => true]
                );

                $modelOrder = 1;
                foreach ($seriesGroups as $seriesName => $models) {
                    foreach ($models as $modelName) {
                        RepairModel::updateOrCreate(
                            [
                                'repair_brand_id' => $brand->id,
                                'name' => $modelName,
                            ],
                            [
                                'series' => $seriesName,
                                'sort_order' => $modelOrder++,
                                'status' => true,
                            ]
                        );
                    }
                }
            }
        });
    }
}
