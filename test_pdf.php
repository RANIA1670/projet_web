<?php
require "models/PdfGenerator.php";
$pdf = new PdfGenerator();
$pdf->addTitle("Test PDF");
$pdf->addLine("Ligne 1");
$pdf->addLine("Ligne 2");
$ref = new ReflectionMethod(PdfGenerator::class, "buildPdf");
$ref->setAccessible(true);
file_put_contents("tmp_test.pdf", $ref->invoke($pdf));
echo "done";
