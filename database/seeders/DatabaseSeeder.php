<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Categorie;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles and store them in variables
        $adminRole = Role::create(['name' => 'Administrateur']);
        $formateurRole = Role::create(['name' => 'Formateur']);
        Role::create(['name' => 'Client']);

        // 2. Create the Admin User
        $admin = User::create([
            'nom' => 'Elharchaouy',
            'prenom' => 'Ilham',
            'email' => 'admin@color4y.com',
            'telephone' => '0600000000',
            'password' => bcrypt('password'),
            'status' => 'actif'
        ]);
        
        // Native Laravel way to assign a relationship using the pivot table
        $admin->roles()->attach($adminRole->id);

        // 3. Create the Formateur User
        $formateur = User::create([
            'nom' => 'Da Vinci',
            'prenom' => 'Leonardo',
            'email' => 'formateur@color4y.com',
            'password' => bcrypt('password'),
            'status' => 'actif'
        ]);
        
        // Native Laravel way to assign a relationship using the pivot table
        $formateur->roles()->attach($formateurRole->id);

        // 4. Create Categories
        Categorie::create(['name' => 'Livres d’Art']);
        Categorie::create(['name' => 'Tableaux']);
        Categorie::create(['name' => 'Matériel de Peinture']);
    }
}