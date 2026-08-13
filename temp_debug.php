<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Roles tenant:\n";
var_dump(\App\Services\RoleService::getTenantRoles());

echo "\nTodos os roles:\n";
\Spatie\Permission\Models\Role::all()->each(function($r) {
    echo $r->name . ' (' . ($r->type ?? 'null') . ")\n";
});