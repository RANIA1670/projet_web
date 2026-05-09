<?php
// ================================================
//  FICHIER  : models/EventModel.php
//  RÔLE     : Requêtes SQL et entité pour la table event
// ================================================

require_once __DIR__ . '/Model.php';

class EventModel extends Model
{
    private ?int $idEvent;
    private string $titre;
    private string $description;
    private string $dateEvent;
    private string $lieu;
    private int $idSponsor;

    public function __construct(
        string $titre = '',
        string $description = '',
        string $dateEvent = '',
        string $lieu = '',
        int $idSponsor = 0,
        ?int $idEvent = null
    ) {
        parent::__construct();
        $this->idEvent     = $idEvent;
        $this->titre       = $titre;
        $this->description = $description;
        $this->dateEvent   = $dateEvent;
        $this->lieu        = $lieu;
        $this->idSponsor   = $idSponsor;
    }

    public function __destruct()
    {
        unset($this->pdo, $this->titre, $this->description, $this->dateEvent, $this->lieu, $this->idSponsor, $this->idEvent);
    }

    public function getIdEvent(): ?int
    {
        return $this->idEvent;
    }

    public function setIdEvent(int $idEvent): self
    {
        $this->idEvent = $idEvent;
        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateEvent(): string
    {
        return $this->dateEvent;
    }

    public function setDateEvent(string $dateEvent): self
    {
        $this->dateEvent = $dateEvent;
        return $this;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getIdSponsor(): int
    {
        return $this->idSponsor;
    }

    public function setIdSponsor(int $idSponsor): self
    {
        $this->idSponsor = $idSponsor;
        return $this;
    }

    public function create(): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO event (titre, description, date_event, lieu, id_sponsor)
             VALUES (:titre, :description, :date_event, :lieu, :id_sponsor)'
        );

        return $stmt->execute([
            ':titre'       => $this->titre,
            ':description' => $this->description,
            ':date_event'  => $this->dateEvent,
            ':lieu'        => $this->lieu,
            ':id_sponsor'  => $this->idSponsor,
        ]);
    }

    public function update(): bool
    {
        if ($this->idEvent === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE event
             SET titre = :titre, description = :description, date_event = :date_event,
                 lieu = :lieu, id_sponsor = :id_sponsor
             WHERE id_event = :id'
        );

        return $stmt->execute([
            ':titre'       => $this->titre,
            ':description' => $this->description,
            ':date_event'  => $this->dateEvent,
            ':lieu'        => $this->lieu,
            ':id_sponsor'  => $this->idSponsor,
            ':id'          => $this->idEvent,
        ]);
    }

    public function delete(): bool
    {
        if ($this->idEvent === null) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM event WHERE id_event = :id');
        return $stmt->execute([':id' => $this->idEvent]);
    }

    public function save(): bool
    {
        return $this->idEvent === null ? $this->create() : $this->update();
    }

    public function toArray(): array
    {
        return [
            'id_event'     => $this->idEvent,
            'titre'        => $this->titre,
            'description'  => $this->description,
            'date_event'   => $this->dateEvent,
            'lieu'         => $this->lieu,
            'id_sponsor'   => $this->idSponsor,
        ];
    }

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT e.*, s.nom AS nom_sponsor
                FROM event e
                JOIN sponsor s ON e.id_sponsor = s.id_sponsor
                WHERE 1=1';

        $params = [];

        if (!empty($filters['keyword'])) {
            $keyword = '%' . strtolower($filters['keyword']) . '%';
            $sql .= ' AND (LOWER(e.titre) LIKE :keyword_titre OR LOWER(e.description) LIKE :keyword_description OR LOWER(e.lieu) LIKE :keyword_lieu OR LOWER(s.nom) LIKE :keyword_sponsor)';
            $params[':keyword_titre']       = $keyword;
            $params[':keyword_description'] = $keyword;
            $params[':keyword_lieu']        = $keyword;
            $params[':keyword_sponsor']     = $keyword;
        }

        if (!empty($filters['lieu'])) {
            $sql .= ' AND LOWER(e.lieu) LIKE :lieu';
            $params[':lieu'] = '%' . strtolower($filters['lieu']) . '%';
        }

        if (!empty($filters['id_sponsor']) && (int)$filters['id_sponsor'] > 0) {
            $sql .= ' AND e.id_sponsor = :id_sponsor';
            $params[':id_sponsor'] = (int)$filters['id_sponsor'];
        }

        if (!empty($filters['date_from']) && $this->isValidDate($filters['date_from'])) {
            $sql .= ' AND e.date_event >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to']) && $this->isValidDate($filters['date_to'])) {
            $sql .= ' AND e.date_event <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY e.date_event ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findEventsInDays(int $days): array
    {
        $days = max(0, (int)$days);
        $sql = 'SELECT e.*, s.nom AS nom_sponsor
                FROM event e
                JOIN sponsor s ON e.id_sponsor = s.id_sponsor
                WHERE e.date_event BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL ' . $days . ' DAY)
                ORDER BY e.date_event ASC';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function countUpcoming(int $days = 7): int
    {
        $days = max(0, (int)$days);
        $sql = 'SELECT COUNT(*) FROM event
                WHERE date_event BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL ' . $days . ' DAY)';

        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function getTopSponsors(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.nom, COUNT(e.id_event) AS event_count
             FROM sponsor s
             JOIN event e ON s.id_sponsor = e.id_sponsor
             GROUP BY s.id_sponsor
             ORDER BY event_count DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPopularEvents(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id_event, e.titre, e.date_event, e.lieu, s.nom AS nom_sponsor,
                    COUNT(p.id_participation) AS participants
             FROM event e
             JOIN sponsor s ON e.id_sponsor = s.id_sponsor
             LEFT JOIN participation p ON e.id_event = p.id_event
             GROUP BY e.id_event
             ORDER BY participants DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, s.nom AS nom_sponsor
             FROM event e
             JOIN sponsor s ON e.id_sponsor = s.id_sponsor
             WHERE e.id_event = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM event')->fetchColumn();
    }

    public function countEventsWithParticipants(): int
    {
        $stmt = $this->pdo->query(
            'SELECT COUNT(DISTINCT e.id_event)
             FROM event e
             JOIN participation p ON e.id_event = p.id_event'
        );
        return (int) $stmt->fetchColumn();
    }
}
