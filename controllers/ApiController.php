<?php
// ================================================
//  FICHIER  : controllers/ApiController.php
//  RÔLE     : Retourne des données JSON pour le calendrier
// ================================================

require_once __DIR__ . '/../models/EventModel.php';

class ApiController
{
    /**
     * Retourne les événements au format JSON FullCalendar.
     * Route : ?page=api_events_json
     */
    public function eventsJson(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');

        $model  = new EventModel();
        $events = $model->findAll();

        $output = [];
        foreach ($events as $ev) {
            // Couleur selon la date (passé / futur)
            $isPast = strtotime($ev['date_event']) < strtotime('today');
            $color  = $isPast ? '#94a3b8' : '#1e3a5f';

            $output[] = [
                'id'            => $ev['id_event'],
                'title'         => $ev['titre'],
                'start'         => $ev['date_event'],
                'url'           => 'index.php?page=front_event_detail&id=' . $ev['id_event'],
                'backgroundColor' => $color,
                'borderColor'   => $isPast ? '#64748b' : '#E67E22',
                'extendedProps' => [
                    'lieu'      => $ev['lieu'],
                    'sponsor'   => $ev['nom_sponsor'],
                    'description' => mb_substr($ev['description'], 0, 120) . '...',
                ],
            ];
        }

        echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
