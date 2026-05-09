<?php
// ================================================
//  FICHIER  : models/TicketGenerator.php
//  RÔLE     : Génère un billet PDF avec QR Code
//             pour une participation confirmée
// ================================================

class TicketGenerator
{
    // Génère le contenu brut PDF du billet
    public function generateTicket(array $participant, array $event): string
    {
        $nom       = $participant['nom_participant']   ?? 'Inconnu';
        $email     = $participant['email_participant'] ?? '';
        $idP       = $participant['id_participation'] ?? '0';
        $titre     = $event['titre']     ?? 'Événement';
        $date      = isset($event['date_event'])
                        ? date('d/m/Y', strtotime($event['date_event']))
                        : '';
        $lieu      = $event['lieu']      ?? '';
        $sponsor   = $event['nom_sponsor'] ?? '';
        $idE       = $event['id_event']  ?? '0';

        // Code unique du billet
        $code = 'EV' . str_pad($idE, 3, '0', STR_PAD_LEFT)
               . '-PART' . str_pad($idP, 4, '0', STR_PAD_LEFT);

        // URL du QR code (API publique gratuite — aucune lib requise)
        $qrData    = urlencode("CityZen|Billet:{$code}|Participant:{$nom}|Event:{$titre}|Date:{$date}|Lieu:{$lieu}");
        $qrUrl     = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$qrData}";

        // Télécharger l'image QR code
        $qrImageData = @file_get_contents($qrUrl);
        $qrBase64    = $qrImageData ? base64_encode($qrImageData) : null;

        return $this->buildPdf($nom, $email, $code, $titre, $date, $lieu, $sponsor, $qrBase64);
    }

    // Sortie directe du PDF dans le navigateur (téléchargement)
    public function output(array $participant, array $event): void
    {
        while (ob_get_level()) {
            @ob_end_clean();
        }

        $pdf = $this->generateTicket($participant, $event);

        $filename = 'billet_' . ($participant['id_participation'] ?? 'x') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        echo $pdf;
        flush();
        exit;
    }

    // ---- Helpers PDF ----

    private function esc(string $text): string
    {
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return preg_replace('/[\x00-\x1f]/', ' ', $text);
    }

    private function buildPdf(
        string $nom,
        string $email,
        string $code,
        string $titre,
        string $date,
        string $lieu,
        string $sponsor,
        ?string $qrBase64
    ): string {

        // ---- Préparer les streams ----

        // Stream page principale
        $pageContent = $this->buildPageStream($nom, $email, $code, $titre, $date, $lieu, $sponsor);

        // Stream image QR (XObject JPEG si disponible)
        $hasQr      = ($qrBase64 !== null);
        $qrRaw      = $hasQr ? base64_decode($qrBase64) : '';
        $qrLen      = strlen($qrRaw);

        // ---- Numérotation des objets ----
        // 1 = Catalog
        // 2 = Pages
        // 3 = Font Helvetica
        // 4 = Font Helvetica-Bold
        // 5 = Page dict
        // 6 = Page content stream
        // 7 = XObject image QR (si dispo)

        $objCount   = $hasQr ? 7 : 6;

        $objects    = [];

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [5 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        // Ressources de la page
        $resourcesDict = "/Font << /F1 3 0 R /F2 4 0 R >>";
        if ($hasQr) {
            $resourcesDict .= " /XObject << /QR 7 0 R >>";
        }

        $objects[5] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842]"
                    . " /Resources << {$resourcesDict} >>"
                    . " /Contents 6 0 R >>";

        $pageLen        = strlen($pageContent);
        $objects[6]     = "<< /Length {$pageLen} >>\nstream\n{$pageContent}endstream";

        if ($hasQr) {
            $objects[7] = "<< /Type /XObject /Subtype /Image /Width 150 /Height 150"
                        . " /ColorSpace /DeviceRGB /BitsPerComponent 8"
                        . " /Filter /DCTDecode /Length {$qrLen} >>\nstream\n{$qrRaw}endstream";
        }

        // ---- Assemblage ----
        $pdf    = "%PDF-1.4\n";
        $xref   = [];

        foreach ($objects as $n => $body) {
            $xref[$n] = strlen($pdf);
            $pdf .= "{$n} 0 obj\n{$body}\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 " . ($objCount + 1) . "\n";
        $pdf .= sprintf("%010d 65535 f\n", 0);
        foreach ($xref as $off) {
            $pdf .= sprintf("%010d 00000 n\n", $off);
        }
        $pdf .= "trailer\n<< /Size " . ($objCount + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }

    private function buildPageStream(
        string $nom,
        string $email,
        string $code,
        string $titre,
        string $date,
        string $lieu,
        string $sponsor
    ): string {
        // Billet : fond couleur + infos

        $s  = "";

        // === Fond dégradé simulé (bleu foncé) ===
        $s .= "q\n";
        $s .= "0.11 0.18 0.35 rg\n";     // couleur marine
        $s .= "30 680 535 130 re f\n";   // rectangle header
        $s .= "Q\n";

        // === Bande orange déco ===
        $s .= "q\n";
        $s .= "0.9 0.47 0.13 rg\n";      // orange
        $s .= "30 676 535 6 re f\n";
        $s .= "Q\n";

        // === Logo / Titre du site ===
        $s .= "BT\n";
        $s .= "/F2 28 Tf\n";
        $s .= "1 1 1 rg\n";              // blanc
        $s .= "50 770 Td\n";
        $s .= "(CityZen) Tj\n";
        $s .= "/F1 11 Tf\n";
        $s .= "1 0.68 0.26 rg\n";        // orange clair
        $s .= "0 -22 Td\n";
        $s .= "(BILLET D'ENTREE OFFICIEL) Tj\n";
        $s .= "ET\n";

        // === Nom de l'événement ===
        $s .= "BT\n";
        $s .= "/F2 18 Tf\n";
        $s .= "0.11 0.18 0.35 rg\n";
        $s .= "50 650 Td\n";
        $s .= "(" . $this->esc($titre) . ") Tj\n";
        $s .= "ET\n";

        // === Ligne séparatrice ===
        $s .= "q\n";
        $s .= "0.8 0.8 0.8 RG\n";
        $s .= "1 w\n";
        $s .= "50 638 m 545 638 l S\n";
        $s .= "Q\n";

        // === Informations participant ===
        $infoY = 618;
        $lineH = 22;

        $fields = [
            ['Participant :', $nom],
            ['Email :', $email],
            ['Date :', $date],
            ['Lieu :', $lieu],
            ['Sponsor :', $sponsor],
        ];

        foreach ($fields as [$label, $value]) {
            // Label gras gris
            $s .= "BT\n";
            $s .= "/F2 10 Tf\n";
            $s .= "0.4 0.4 0.4 rg\n";
            $s .= "50 {$infoY} Td\n";
            $s .= "({$this->esc($label)}) Tj\n";
            $s .= "ET\n";

            // Valeur noire
            $s .= "BT\n";
            $s .= "/F1 11 Tf\n";
            $s .= "0.11 0.18 0.35 rg\n";
            $s .= "160 {$infoY} Td\n";
            $s .= "(" . $this->esc($value) . ") Tj\n";
            $s .= "ET\n";

            $infoY -= $lineH;
        }

        // === Zone QR code ===
        $s .= "q\n";
        $s .= "Do\n";   // placeholder — sera ignoré si pas de XObject lié ici
        $s .= "Q\n";

        // Dessiner QR (position droite)
        $s .= "q\n";
        $s .= "150 0 0 150 380 480 cm\n";
        $s .= "/QR Do\n";
        $s .= "Q\n";

        // === Cadre QR ===
        $s .= "q\n";
        $s .= "0.8 0.8 0.8 RG\n";
        $s .= "1 w\n";
        $s .= "375 475 160 160 re S\n";
        $s .= "Q\n";

        // === Code du billet ===
        $s .= "q\n";
        $s .= "0.11 0.18 0.35 rg\n";
        $s .= "30 440 535 50 re f\n";
        $s .= "Q\n";

        $s .= "BT\n";
        $s .= "/F2 10 Tf\n";
        $s .= "1 1 1 rg\n";
        $s .= "50 472 Td\n";
        $s .= "(CODE BILLET) Tj\n";
        $s .= "ET\n";

        $s .= "BT\n";
        $s .= "/F2 18 Tf\n";
        $s .= "1 0.68 0.26 rg\n";
        $s .= "50 450 Td\n";
        $s .= "(" . $this->esc($code) . ") Tj\n";
        $s .= "ET\n";

        // === Footer ===
        $s .= "BT\n";
        $s .= "/F1 9 Tf\n";
        $s .= "0.5 0.5 0.5 rg\n";
        $s .= "50 60 Td\n";
        $s .= "(Ce billet est nominatif et non transferable. Presentez-le a l'entree de l'evenement.) Tj\n";
        $s .= "0 -14 Td\n";
        $s .= "(CityZen - Plateforme de gestion d'evenements) Tj\n";
        $s .= "ET\n";

        return $s;
    }
}
