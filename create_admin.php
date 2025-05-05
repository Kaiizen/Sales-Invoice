<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use Illuminate\Support\Facades\Hash;

// Check if admin user already exists
$existingAdmin = User::where('email', 'admin@mail.com')->first();

if ($existingAdmin) {
    echo "Admin user already exists. Updating password...\n";
    $existingAdmin->password = Hash::make('codeastro.com');
    $existingAdmin->save();
    echo "Password updated successfully.\n";
} else {
    echo "Creating new admin user...\n";
    User::create([
        'f_name' => 'Admin',
        'l_name' => 'User',
        'email' => 'admin@mail.com',
        'password' => Hash::make('codeastro.com'),
        'role' => 'admin',
        'image' => 'default.jpg'
    ]);
    echo "Admin user created successfully.\n";
}