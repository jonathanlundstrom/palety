<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Enumerables\ImportCategory;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $items = [
            [
                'en' => 'Toys',
                'ua' => 'Іграшки',
                'cat' => ImportCategory::OTHER,
            ],
            [
                'en' => 'Medical supplies',
                'ua' => 'Медичні вироби',
                'cat' => ImportCategory::MEDICAL,
            ],
            [
                'en' => 'Bed linen',
                'ua' => 'Постільна білизна',
                'cat' => ImportCategory::OTHER,
            ],
            [
                'en' => 'Pet food',
                'ua' => 'Корм для тварин',
                'cat' => ImportCategory::OTHER,
            ],
            [
                'en' => 'Food',
                'ua' => 'Їжа',
                'cat' => ImportCategory::FOOD,
            ],
            [
                'en' => 'Work clothes',
                'ua' => 'Робочий одяг',
                'cat' => ImportCategory::CLOTHING,
            ],
            [
                'en' => 'Blankets',
                'ua' => 'Ковдри',
                'cat' => ImportCategory::OTHER,
            ],
        ];

        foreach ($items as $item) {
            Content::updateOrCreate([
                'label_en' => $item['en'],
                'label_ua' => $item['ua'],
                'category' => $item['cat']->name,
            ]);
        }
    }
}
