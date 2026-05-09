<?php
// ================================================
//  FICHIER  : models/AvisModel.php
//  RÔLE     : Gestion des avis et notations des événements
//
//  SQL À EXÉCUTER UNE FOIS :
//  CREATE TABLE IF NOT EXISTS avis (
//      id_avis        INT AUTO_INCREMENT PRIMARY KEY,
//      id_event       INT NOT NULL,
//      id_participation INT NOT NULL,
//      note           INT NOT NULL CHECK (note BETWEEN 1 AND 5),
//      commentaire    TEXT,
//      date_avis      DATETIME DEFAULT CURRENT_TIMESTAMP,
//      approuve       TINYINT(1) DEFAULT 0,
//      FOREIGN KEY (id_event) REFERENCES event(id_event) ON DELETE CASCADE,
//      FOREIGN KEY (id_participation) REFERENCES participation(id_participation) ON DELETE CASCADE,
//      UNIQUE KEY unique_avis (id_event, id_participation)
//  );
// ================================================

require_once __DIR__ . '/Model.php';

class AvisModel extends Model
{
    private ?int    $idAvis;
    private int     $idEvent;
    private int     $idParticipation;
    private int     $note;
    private string  $commentaire;
    private int     $approuve;

    public function __construct(
        int    $idEvent          = 0,
        int    $idParticipation  = 0,
        int    $note             = 5,
        string $commentaire      = '',
        int    $approuve         = 0,
        ?int   $idAvis           = null
    ) {
        parent::__construct();
        $this->idAvis          = $idAvis;
        $this->idEvent         = $idEvent;
        $this->idParticipation = $idParticipation;
        $this->note            = max(1, min(5, $note));
        $this->commentaire     = $commentaire;
        $this->approuve        = $approuve;
    }

    public function __destruct()
    {
        unset($this->pdo, $this->idAvis, $this->idEvent, $this->idParticipation,
              $this->note, $this->commentaire, $this->approuve);
    }

    // ---- Getters / Setters ----

    public function getIdAvis(): ?int     { return $this->idAvis; }
    public function getIdEvent(): int     { return $this->idEvent; }
    public function getIdParticipation(): int { return $this->idParticipation; }
    public function getNote(): int        { return $this->note; }
    public function getCommentaire(): string { return $this->commentaire; }
    public function getApprouve(): int    { return $this->approuve; }

    public function setNote(int $note): self
    {
        $this->note = max(1, min(5, $note));
        return $this;
    }
    public function setCommentaire(string $c): self { $this->commentaire = $c; return $this; }
    public function setApprouve(int $a): self       { $this->approuve    = $a; return $this; }

    // ---- CRUD ----

    public function createTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS avis (
                id_avis        INT AUTO_INCREMENT PRIMARY KEY,
                id_event       INT NOT NULL,
                id_participation INT NOT NULL,
                note           INT NOT NULL DEFAULT 5,
                commentaire    TEXT,
                date_avis      DATETIME DEFAULT CURRENT_TIMESTAMP,
                approuve       TINYINT(1) DEFAULT 0,
                FOREIGN KEY (id_event) REFERENCES event(id_event) ON DELETE CASCADE,
                FOREIGN KEY (id_participation) REFERENCES participation(id_participation) ON DELETE CASCADE,
                UNIQUE KEY unique_avis (id_event, id_participation)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function create(): bool
    {
        // Créer la table si elle n'existe pas
        $this->createTable();

        $stmt = $this->pdo->prepare(
            'INSERT INTO avis (id_event, id_participation, note, commentaire, approuve)
             VALUES (:id_event, :id_participation, :note, :commentaire, :approuve)'
        );
        return $stmt->execute([
            ':id_event'        => $this->idEvent,
            ':id_participation'=> $this->idParticipation,
            ':note'            => $this->note,
            ':commentaire'     => $this->commentaire,
            ':approuve'        => $this->approuve,
        ]);
    }

    public function approuver(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE avis SET approuve = 1 WHERE id_avis = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function rejeter(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM avis WHERE id_avis = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function delete(): bool
    {
        if ($this->idAvis === null) return false;
        $stmt = $this->pdo->prepare('DELETE FROM avis WHERE id_avis = :id');
        return $stmt->execute([':id' => $this->idAvis]);
    }

    // ---- Queries ----

    /** Tous les avis (back-office) */
    public function findAll(array $filters = []): array
    {
        $this->createTable();

        $sql = 'SELECT a.*, e.titre AS titre_event,
                       p.nom_participant, p.email_participant
                FROM avis a
                JOIN event e ON a.id_event = e.id_event
                JOIN participation p ON a.id_participation = p.id_participation
                WHERE 1=1';
        $params = [];

        if (isset($filters['approuve']) && $filters['approuve'] !== '') {
            $sql .= ' AND a.approuve = :approuve';
            $params[':approuve'] = (int)$filters['approuve'];
        }
        if (!empty($filters['id_event'])) {
            $sql .= ' AND a.id_event = :id_event';
            $params[':id_event'] = (int)$filters['id_event'];
        }

        $sql .= ' ORDER BY a.date_avis DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Avis approuvés pour un événement (front) */
    public function findByEvent(int $idEvent): array
    {
        $this->createTable();

        $stmt = $this->pdo->prepare(
            'SELECT a.*, p.nom_participant
             FROM avis a
             JOIN participation p ON a.id_participation = p.id_participation
             WHERE a.id_event = :id AND a.approuve = 1
             ORDER BY a.date_avis DESC'
        );
        $stmt->execute([':id' => $idEvent]);
        return $stmt->fetchAll();
    }

    /** Note moyenne d'un événement */
    public function getAverageNote(int $idEvent): float
    {
        $this->createTable();

        $stmt = $this->pdo->prepare(
            'SELECT AVG(note) FROM avis WHERE id_event = :id AND approuve = 1'
        );
        $stmt->execute([':id' => $idEvent]);
        return round((float)$stmt->fetchColumn(), 1);
    }

    /** Nombre d'avis approuvés pour un événement */
    public function countByEvent(int $idEvent): int
    {
        $this->createTable();

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM avis WHERE id_event = :id AND approuve = 1'
        );
        $stmt->execute([':id' => $idEvent]);
        return (int)$stmt->fetchColumn();
    }

    /** Nombre d'avis en attente (pour badge admin) */
    public function countPending(): int
    {
        $this->createTable();

        return (int)$this->pdo
            ->query('SELECT COUNT(*) FROM avis WHERE approuve = 0')
            ->fetchColumn();
    }

    /** Vérifier si un participant a déjà noté un événement */
    public function dejaNote(int $idEvent, int $idParticipation): bool
    {
        $this->createTable();

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM avis WHERE id_event = :ie AND id_participation = :ip'
        );
        $stmt->execute([':ie' => $idEvent, ':ip' => $idParticipation]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function toArray(): array
    {
        return [
            'id_avis'          => $this->idAvis,
            'id_event'         => $this->idEvent,
            'id_participation' => $this->idParticipation,
            'note'             => $this->note,
            'commentaire'      => $this->commentaire,
            'approuve'         => $this->approuve,
        ];
    }
}
