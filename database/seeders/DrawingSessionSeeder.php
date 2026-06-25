<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TrainerProfile;
use App\Models\DrawingSession;
use App\Enums\DrawingSessionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DrawingSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainers = TrainerProfile::all();
        $categories = Category::query()->where('type', 'session')->get();

        if ($trainers->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $sessionsData = [
            [
                'title' => 'Initiation à l\'aquarelle florale',
                'description' => 'Apprenez les bases de la technique humide sur humide en peignant des motifs floraux.',
                'starts_at' => now()->addDays(2)->setHour(14)->setMinute(0)->setSecond(0),
                'ends_at' => now()->addDays(2)->setHour(17)->setMinute(0)->setSecond(0),
                'capacity' => 10,
                'registered_count' => 0,
                'price' => 35.00,
                'status' => DrawingSessionStatus::Open,
            ],
            [
                'title' => 'Maîtriser la peinture à l\'huile : Nature morte',
                'description' => 'Un atelier intensif de 4 heures pour comprendre le travail de la lumière et de la matière à l\'huile.',
                'starts_at' => now()->addDays(5)->setHour(9)->setMinute(30)->setSecond(0),
                'ends_at' => now()->addDays(5)->setHour(13)->setMinute(30)->setSecond(0),
                'capacity' => 8,
                'registered_count' => 0,
                'price' => 50.00,
                'status' => DrawingSessionStatus::Open,
            ],
            [
                'title' => 'Croquis au fusain d\'après modèle vivant',
                'description' => 'Pratique du dessin rapide au fusain et à la craie pour capturer le mouvement et les volumes.',
                'starts_at' => now()->addDays(10)->setHour(18)->setMinute(0)->setSecond(0),
                'ends_at' => now()->addDays(10)->setHour(20)->setMinute(30)->setSecond(0),
                'capacity' => 12,
                'registered_count' => 0,
                'price' => 30.00,
                'status' => DrawingSessionStatus::Open,
            ],
            [
                'title' => 'Atelier de dessin d\'art contemporain',
                'description' => 'Découvrez les techniques mixtes modernes pour enrichir vos créations artistiques.',
                'starts_at' => now()->addDays(12)->setHour(10)->setMinute(0)->setSecond(0),
                'ends_at' => now()->addDays(12)->setHour(12)->setMinute(0)->setSecond(0),
                'capacity' => 15,
                'registered_count' => 0,
                'price' => 40.00,
                'status' => DrawingSessionStatus::PendingTrainer,
            ],
        ];

        foreach ($sessionsData as $index => $data) {
            $trainer = $trainers->get($index % $trainers->count());
            $category = $categories->get($index % $categories->count());

            $session = DrawingSession::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'trainer_profile_id' => $trainer->id,
                    'category_id' => $category->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                    'capacity' => $data['capacity'],
                    'registered_count' => $data['registered_count'],
                    'price' => $data['price'],
                    'status' => $data['status'],
                ]
            );

            // Programmatically generate cover image
            try {
                $imagePath = $this->generateDummyImage(
                    rand(50, 200),
                    rand(50, 200),
                    rand(50, 200)
                );

                if (file_exists($imagePath)) {
                    if ($session->getMedia('cover')->isEmpty()) {
                        $session->addMedia($imagePath)->toMediaCollection('cover');
                    } else {
                        unlink($imagePath);
                    }
                }
            } catch (\Exception $e) {
                // Fail gracefully if GD not available
            }
        }
    }

    /**
     * Generate simple solid color image locally using GD
     */
    private function generateDummyImage(int $r, int $g, int $b): string
    {
        $im = imagecreatetruecolor(300, 200);
        $color = imagecolorallocate($im, $r, $g, $b);
        imagefill($im, 0, 0, $color);
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('dummy_sess_', true) . '.png';
        imagepng($im, $tempPath);
        imagedestroy($im);
        return $tempPath;
    }
}
