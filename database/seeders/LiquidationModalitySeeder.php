<?php

namespace Database\Seeders;

use App\Models\LiquidationModality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LiquidationModalitySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $nombres = ['Por maleta', 'Por bin', 'Por jornal'];

        foreach ($nombres as $nombre) {
            LiquidationModality::firstOrCreate(
                ['nombre' => $nombre],
                ['precio_por_unidad' => 0],
            );
        }
    }
}
