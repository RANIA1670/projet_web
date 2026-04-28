<?php
// ================================================
//  FICHIER  : models/ParticipationModel.php
//  RÔLE     : Requêtes SQL et entité pour la table participation
// ================================================

require_once __DIR__ . '/Model.php';

class ParticipationModel extends Model
{
    private ?int $idParticipation;
    private string $nomParticipant;
    private string $emailParticipant;
    private string $numeroParticipant;
    private int $ageParticipant;
    private string $sexeParticipant;
    private int $idEvent;

    public function __construct(
        string $nomParticipant = '',
        string $emailParticipant = '',
        int $idEvent = 0,
        string $numeroParticipant = '',
        int $ageParticipant = 0,
        string $sexeParticipant = '',
        ?int $idParticipation = null
    ) {
        parent::__construct();
        $this->idParticipation   = $idParticipation;
        $this->nomParticipant    = $nomParticipant;
        $this->emailParticipant  = $emailParticipant;
        $this->numeroParticipant = $numeroParticipant;
        $this->ageParticipant    = $ageParticipant;
        $this->sexeParticipant   = $sexeParticipant;
        $this->idEvent           = $idEvent;
    }

    public function __destruct()
    {
        unset(
            $this->pdo,
            $this->idParticipation,
            $this->nomParticipant,
            $this->emailParticipant,
            $this->numeroParticipant,
            $this->ageParticipant,
            $this->sexeParticipant,
            $this->idEvent
        );
    }

    public function getIdParticipation(): ?int
    {
        return $this->idParticipation;
    }

    public function setIdParticipation(int $idParticipation): self
    {
        $this->idParticipation = $idParticipation;
        return $this;
    }

    public function getNomParticipant(): string
    {
        return $this->nomParticipant;
    }

    public function setNomParticipant(string $nomParticipant): self
    {
        $this->nomParticipant = $nomParticipant;
        return $this;
    }

    public function getEmailParticipant(): string
    {
        return $this->emailParticipant;
    }

    public function setEmailParticipant(string $emailParticipant): self
    {
        $this->emailParticipant = $emailParticipant;
        return $this;
    }

    public function getNumeroParticipant(): string
    {
        return $this->numeroParticipant;
    }

    public function setNumeroParticipant(string $numeroParticipant): self
    {
        $this->numeroParticipant = $numeroParticipant;
        return $this;
    }

    public function getAgeParticipant(): int
    {
        return $this->ageParticipant;
    }

    public function setAgeParticipant(int $ageParticipant): self
    {
        $this->ageParticipant = $ageParticipant;
        return $this;
    }

    public function getSexeParticipant(): string
    {
        return $this->sexeParticipant;
    }

    public function setSexeParticipant(string $sexeParticipant): self
    {
        $this->sexeParticipant = $sexeParticipant;
        return $this;
    }

    public function getIdEvent(): int
    {
        return $this->idEvent;
    }

    public function setIdEvent(int $idEvent): self
    {
        $this->idEvent = $idEvent;
        return $this;
    }

    public function create(): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO participation (nom_participant, email_participant, numero_participant, age_participant, sexe_participant, id_event)
             VALUES (:nom, :email, :numero, :age, :sexe, :id_event)'
        );

        return $stmt->execute([
            ':nom'      => $this->nomParticipant,
            ':email'    => $this->emailParticipant,
            ':numero'   => $this->numeroParticipant,
            ':age'      => $this->ageParticipant,
            ':sexe'     => $this->sexeParticipant,
            ':id_event' => $this->idEvent,
        ]);
    }

    public function update(): bool
    {
        if ($this->idParticipation === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE participation
             SET nom_participant = :nom,
                 email_participant = :email,
                 numero_participant = :numero,
                 age_participant = :age,
                 sexe_participant = :sexe,
                 id_event = :id_event
             WHERE id_participation = :id'
        );

        return $stmt->execute([
            ':nom'      => $this->nomParticipant,
            ':email'    => $this->emailParticipant,
            ':numero'   => $this->numeroParticipant,
            ':age'      => $this->ageParticipant,
            ':sexe'     => $this->sexeParticipant,
            ':id_event' => $this->idEvent,
            ':id'       => $this->idParticipation,
        ]);
    }

    public function delete(): bool
    {
        if ($this->idParticipation === null) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM participation WHERE id_participation = :id');
        return $stmt->execute([':id' => $this->idParticipation]);
    }

    public function save(): bool
    {
        return $this->idParticipation === null ? $this->create() : $this->update();
    }

    public function toArray(): array
    {
        return [
            'id_participation'  => $this->idParticipation,
            'nom_participant'   => $this->nomParticipant,
            'email_participant' => $this->emailParticipant,
            'numero_participant'=> $this->numeroParticipant,
            'age_participant'   => $this->ageParticipant,
            'sexe_participant'  => $this->sexeParticipant,
            'id_event'          => $this->idEvent,
        ];
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT p.*, e.titre AS titre_event
                FROM participation p
                JOIN event e ON p.id_event = e.id_event
                WHERE 1=1';

        $params = [];

        if (!empty($filters['keyword'])) {
            $keyword = '%' . strtolower($filters['keyword']) . '%';
            $sql .= ' AND (LOWER(p.nom_participant) LIKE :keyword OR LOWER(p.email_participant) LIKE :keyword OR LOWER(e.titre) LIKE :keyword)';
            $params[':keyword'] = $keyword;
        }

        if (!empty($filters['id_event']) && (int)$filters['id_event'] > 0) {
            $sql .= ' AND p.id_event = :id_event';
            $params[':id_event'] = (int)$filters['id_event'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND e.date_event >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND e.date_event <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY p.id_participation DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByEvent(int $idEvent): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM participation WHERE id_event = :id ORDER BY nom_participant ASC');
        $stmt->execute([':id' => $idEvent]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, e.titre AS titre_event
             FROM participation p
             JOIN event e ON p.id_event = e.id_event
             WHERE p.id_participation = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function existeDeja(string $email = '', int $idEvent = 0, int $excludeId = 0): bool
    {
        if ($email === '') {
            $email   = $this->emailParticipant;
            $idEvent = $this->idEvent;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM participation
             WHERE email_participant = :email AND id_event = :id_event
             AND id_participation != :exclude'
        );
        $stmt->execute([
            ':email'    => $email,
            ':id_event' => $idEvent,
            ':exclude'  => $excludeId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM participation')->fetchColumn();
    }

    public function findRecent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, e.titre AS titre_event
             FROM participation p
             JOIN event e ON p.id_event = e.id_event
             ORDER BY p.id_participation DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
