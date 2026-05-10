<?php
// ================================================
//  FICHIER  : models/PdfGenerator.php
//  RÔLE     : Génération simple de document PDF pour export
// ================================================

class PdfGenerator
{
    private array $lines = [];

    public function addTitle(string $title): self
    {
        $this->addLine($title);
        $this->addLine(str_repeat('-', strlen($title)));
        return $this;
    }

    public function addLine(string $line = ''): self
    {
        $this->lines[] = $line;
        return $this;
    }

    public function addEmptyLine(): self
    {
        return $this->addLine('');
    }

    public function output(string $filename = 'document.pdf'): void
    {
        while (ob_get_level()) {
            @ob_end_clean();
        }

        $pdf = $this->buildPdf();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        echo $pdf;
        flush();
        exit;
    }

    private function escapeText(string $text): string
    {
        $text = utf8_decode($text);
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return preg_replace('/[\x00-\x1f]/', ' ', $text);
    }

    private function buildPdf(): string
    {
        $content = "BT /F1 12 Tf 40 820 Td\n";
        foreach ($this->lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -14 Td\n";
            }
            $content .= '(' . $this->escapeText($line) . ') Tj' . "\n";
        }
        $content .= "ET\n";

        $stream = $content;
        $length = strlen($stream);

        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [4 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[4] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents 5 0 R >>";
        $objects[5] = "<< /Length {$length} >>\nstream\n{$stream}endstream";

        $pdf = "%PDF-1.3\n";
        $xref = [];

        foreach ($objects as $objNumber => $content) {
            $xref[$objNumber] = strlen($pdf);
            $pdf .= $objNumber . " 0 obj\n" . $content . "\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= sprintf("%010d 65535 f\n", 0);
        foreach ($xref as $offset) {
            $pdf .= sprintf("%010d 00000 n\n", $offset);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }
}
