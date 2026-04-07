<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Establishment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstablishmentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $establishments = [
            [
                'nombre' => 'BLAZQUEZ S.R.L',
                'contacts' => [],
            ],
            [
                'nombre' => 'ARBOLAR',
                'contacts' => [],
            ],
        ];

        foreach ($establishments as $data) {
            $establishment = Establishment::firstOrCreate(
                ['nombre' => $data['nombre']],
            );

            foreach ($data['contacts'] as $contactData) {
                Contact::firstOrCreate(
                    [
                        'establishment_id' => $establishment->id,
                        'mail' => $contactData['mail'],
                    ],
                    $contactData + ['establishment_id' => $establishment->id],
                );
            }
        }
    }
}
