<?php
require 'config/config.php';
require 'app/controllers/admin/AdminController.php';
$ref = new ReflectionClass(AdminController::class);
$c = $ref->newInstanceWithoutConstructor();
$pdf = $ref->getMethod('createSimplePdf');
$pdf->setAccessible(true);
$output = $pdf->invoke($c, ['Test ligne','Deuxième ligne']);
file_put_contents('debug_test.pdf', $output);
echo 'written '.strlen($output).' bytes';
