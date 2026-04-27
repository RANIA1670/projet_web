<?php
// ================================================
//  FICHIER  : models/SponsorModel.php
//  RÔLE     : Requêtes SQL et entité pour la table sponsor
// ================================================

require_once __DIR__ . '/Model.php';

class SponsorModel extends Model
{
    private ?int $idSponsor;
    private string $nom;
    private string $email;
    private string $telephone;

    public function __construct(
        string $nom = '',
        string $email = '',
        string $telephone = '',
        ?int $idSponsor = null
    ) {
        parent::__construct();
        $this->idSponsor = $idSponsor;
        $this->nom       = $nom;
        $this->email     = $email;
        $this->telephone = $telephone;
    }

    public function __destruct()
    {
        unset($this->pdo, $this->idSponsor, $this->nom, $this->email, $this->telephone);
    }

    public function getIdSponsor(): ?int
    {
        return $this->idSponsor;
    }

    public function setIdSponsor(int $idSponsor): self
    {
        $this->idSponsor = $idSponsor;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function create(): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sponsor (nom, email, telephone) VALUES (:nom, :email, :telephone)'
        );

        return $stmt->execute([
            ':nom'       => $this->nom,
            ':email'     => $this->email,
            ':telephone' => $this->telephone,
        ]);
    }

    public function update(): bool
    {
        if ($this->idSponsor === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE sponsor SET nom = :nom, email = :email, telephone = :telephone
             WHERE id_sponsor = :id'
        );

        return $stmt->execute([
            ':nom'       => $this->nom,
            ':email'     => $this->email,
            ':telephone' => $this->telephone,
            ':id'        => $this->idSponsor,
        ]);
    }

    public function delete(): bool
    {
        if ($this->idSponsor === null) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM sponsor WHERE id_sponsor = :id');
        return $stmt->execute([':id' => $this->idSponsor]);
    }

    public function save(): bool
    {
        return $this->idSponsor === null ? $this->create() : $this->update();
    }

    public function toArray(): array
    {
        return [
            'id_sponsor' => $this->idSponsor,
            'nom'        => $this->nom,
            'email'      => $this->email,
            'telephone'  => $this->telephone,
        ];
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT * FROM sponsor WHERE 1=1';
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= ' AND (LOWER(nom) LIKE :keyword OR LOWER(email) LIKE :keyword OR LOWER(telephone) LIKE :keyword)';
            $params[':keyword'] = '%' . strtolower($filters['keyword']) . '%';
        }

        $sql .= ' ORDER BY nom ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countActiveSponsors(): int
    {
        return (int) $this->pdo->query(
            'SELECT COUNT(DISTINCT s.id_sponsor)
             FROM sponsor s
             JOIN event e ON s.id_sponsor = e.id_sponsor'
        )->fetchColumn();
    }

    public function countSponsors(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM sponsor')->fetchColumn();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sponsor WHERE id_sponsor = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
