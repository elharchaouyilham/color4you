<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the default product category tree.
     */
    public function run(): void
    {
        $tree = [
            'peinture' => [
                'name' => 'Peinture',
                'children' => [
                    'acrylique' => 'Acrylique',
                    'huile' => 'Huile',
                    'aquarelle' => 'Aquarelle',
                    'gouache' => 'Gouache',
                ],
            ],
            'tableaux' => [
                'name' => 'Tableaux',
                'children' => [
                    'moderne' => 'Moderne',
                    'abstrait' => 'Abstrait',
                    'classique' => 'Classique',
                    'decoratif' => 'Decoratif',
                ],
            ],
            'livres-art' => [
                'name' => "Livres d'art",
                'children' => [
                    'histoire-art' => "Histoire de l'art",
                    'manuels-techniques' => 'Manuels de techniques',
                ],
            ],
            'materiel-artistique' => [
                'name' => 'Materiel artistique',
                'children' => [
                    'pinceaux' => 'Pinceaux',
                    'toiles' => 'Toiles',
                    'chevalets' => 'Chevalets',
                    'crayons' => 'Crayons',
                    'feutres' => 'Feutres',
                ],
            ],
        ];

        foreach ($tree as $parentSlug => $parentData) {
            $parent = Category::query()->updateOrCreate([
                'slug' => $parentSlug,
            ], [
                'name' => $parentData['name'],
                'description' => null,
                'parent_id' => null,
                'type' => 'product',
                'is_active' => true,
            ]);

            foreach ($parentData['children'] as $childSlug => $childName) {
                Category::query()->updateOrCreate([
                    'slug' => $childSlug,
                ], [
                    'name' => $childName,
                    'description' => null,
                    'parent_id' => $parent->id,
                    'type' => 'product',
                    'is_active' => true,
                ]);
            }
        }

        $sessionCategories = [
            'aquarelle-session' => 'Aquarelle',
            'huile-session' => 'Huile',
            'fusain-session' => 'Fusain',
        ];

        foreach ($sessionCategories as $slug => $name) {
            Category::query()->updateOrCreate([
                'slug' => $slug,
            ], [
                'name' => $name,
                'description' => 'Session de dessin - ' . $name,
                'parent_id' => null,
                'type' => 'session',
                'is_active' => true,
            ]);
        }
    }
}
