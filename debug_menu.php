<?php

use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new AccessControlService();
$user = User::where('role', 'superuser')->first();

if (!$user) {
    echo "No superuser found.\n";
    exit;
}

echo "Checking menus for user: " . $user->name . " (" . $user->email . ")\n";

$hierarchy = $service->getMenuHierarchy($user);

function printTree($nodes, $depth = 0) {
    foreach ($nodes as $node) {
        echo str_repeat("  ", $depth) . "- " . $node['name'] . " (Order: " . $node['order'] . ", ID: " . $node['id'] . ")\n";
        if (!empty($node['children'])) {
            printTree($node['children'], $depth + 1);
        } else {
             // Debug: check existence of children in DB
             $childrenCount = \App\Models\Menu::where('parent_id', $node['id'])->count();
             if ($childrenCount > 0) {
                 echo str_repeat("  ", $depth + 1) . "[!] Has $childrenCount children in DB but none in tree.\n";
                 // Show raw child query check
                 $kids = \App\Models\Menu::where('parent_id', $node['id'])->get();
                 foreach($kids as $k) {
                     echo str_repeat("  ", $depth + 2) . "? Child in DB: " . $k->name . " (Order: " . $k->order . ")\n"; 
                 }
             }
        }
    }
}

printTree($hierarchy);
