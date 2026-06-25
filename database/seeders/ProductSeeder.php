<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Enums\ProductStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::query()->where('type', 'product')->whereNotNull('parent_id')->get();
        if ($categories->isEmpty()) {
            $categories = Category::query()->where('type', 'product')->get();
        }

        $productsData = [
            [
                'name' => 'Coffret Peinture Huile Extra-Fine',
                'description' => 'Un coffret complet de 12 tubes de peinture à l\'huile extra-fine pour artistes exigeants.',
                'price' => 59.90,
                'stock_quantity' => 15,
                'reserved_quantity' => 2,
                'status' => ProductStatus::Available,
                'created_at' => now()->subDays(1),
            ],
            [
                'name' => 'Bloc Papier Aquarelle 300g',
                'description' => 'Bloc de 20 feuilles de papier coton 300g grain fin pour techniques humides.',
                'price' => 24.50,
                'stock_quantity' => 40,
                'reserved_quantity' => 0,
                'status' => ProductStatus::Available,
                'created_at' => now()->subMinutes(30),
            ],
            [
                'name' => 'Set de 5 Pinceaux Aquarelle',
                'description' => 'Pinceaux en poils synthétiques de haute qualité pour aquarelle et lavis.',
                'price' => 18.90,
                'stock_quantity' => 25,
                'reserved_quantity' => 1,
                'status' => ProductStatus::Available,
                'created_at' => now()->subHours(2),
            ],
            [
                'name' => 'Chevalet d\'atelier en Hêtre',
                'description' => 'Chevalet robuste en bois de hêtre, réglable en hauteur et inclinaison.',
                'price' => 120.00,
                'stock_quantity' => 5,
                'reserved_quantity' => 0,
                'status' => ProductStatus::Available,
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Boîte de Crayons Fusain Gradués',
                'description' => 'Boîte métallique contenant 6 crayons fusains de duretés assorties.',
                'price' => 12.50,
                'stock_quantity' => 30,
                'reserved_quantity' => 3,
                'status' => ProductStatus::Available,
                'created_at' => now()->subMinutes(5),
            ],
        ];

        foreach ($productsData as $index => $data) {
            $category = $categories->get($index % $categories->count());

            $product = Product::query()->firstOrNew([
                'reference' => 'REF-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT)
            ]);
            
            $product->fill([
                'category_id' => $category ? $category->id : null,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'reserved_quantity' => $data['reserved_quantity'],
                'status' => $data['status'],
            ]);

            $product->created_at = $data['created_at'];
            $product->updated_at = $data['created_at'];
            $product->save();

            // Programmatically generate a simple solid color image
            try {
                $imagePath = $this->generateDummyImage(
                    rand(50, 200),
                    rand(50, 200),
                    rand(50, 200)
                );

                if (file_exists($imagePath)) {
                    if ($product->getMedia('images')->isEmpty()) {
                        $product->addMedia($imagePath)->toMediaCollection('images');
                    } else {
                        unlink($imagePath);
                    }
                }
            } catch (\Exception $e) {
                // Fail gracefully if GD is not available or throws error
            }
        }
    }

    /**
     * Generate simple solid color image locally using GD
     */
    private function generateDummyImage(int $r, int $g, int $b): string
    {
        $im = imagecreatetruecolor(200, 200);
        $color = imagecolorallocate($im, $r, $g, $b);
        imagefill($im, 0, 0, $color);
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('dummy_prod_', true) . '.png';
        imagepng($im, $tempPath);
        imagedestroy($im);
        return $tempPath;
    }
}
