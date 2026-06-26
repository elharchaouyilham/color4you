<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Administrateur
        $admin = User::create([
            'nom' => 'Admin',
            'prenom' => 'Color4Y',
            'email' => 'admin@color4y.com',
            'telephone' => '0601020304',
            'password' => Hash::make('password'),
            'status' => 'actif',
        ]);
        $admin->assignRole('Administrateur');

        // 2. Formateurs
        $formateur1 = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@color4y.com',
            'telephone' => '0602030405',
            'password' => Hash::make('password'),
            'status' => 'actif',
        ]);
        $formateur1->assignRole('Formateur');

        $formateur2 = User::create([
            'nom' => 'Bernard',
            'prenom' => 'Marie',
            'email' => 'marie.bernard@color4y.com',
            'telephone' => '0603040506',
            'password' => Hash::make('password'),
            'status' => 'actif',
        ]);
        $formateur2->assignRole('Formateur');

        // 3. Clients
        $client1 = User::create([
            'nom' => 'Martin',
            'prenom' => 'Alice',
            'email' => 'client@color4y.com',
            'telephone' => '0604050607',
            'password' => Hash::make('password'),
            'status' => 'actif',
        ]);
        $client1->assignRole('Client');

        $client2 = User::create([
            'nom' => 'Petit',
            'prenom' => 'Lucas',
            'email' => 'lucas.petit@color4y.com',
            'telephone' => '0605060708',
            'password' => Hash::make('password'),
            'status' => 'actif',
        ]);
        $client2->assignRole('Client');

        // Banned Client for testing
        $bannedClient = User::create([
            'nom' => 'Banni',
            'prenom' => 'Jean-Luc',
            'email' => 'banned@color4y.com',
            'telephone' => '0606070809',
            'password' => Hash::make('password'),
            'status' => 'banni',
        ]);
        $bannedClient->assignRole('Client');
    }
}
