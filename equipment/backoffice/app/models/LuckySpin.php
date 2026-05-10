<?php

declare(strict_types=1);

class LuckySpin
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function todaySpinForUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.id, s.user_id, s.spin_date, s.outcome, s.discount_code_id, s.created_at,
                    c.code, c.discount_percent, c.status AS code_status, c.valid_until
             FROM equipment_lucky_spin s
             LEFT JOIN equipment_discount_code c ON c.id = s.discount_code_id
             WHERE s.user_id = :u AND s.spin_date = CURDATE()
             LIMIT 1"
        );
        $stmt->execute([':u' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>
     */
    public function spinToday(int $userId): array
    {
        $existing = $this->todaySpinForUser($userId);
        if ($existing !== null) {
            return [
                'already_spun' => true,
                'outcome' => (string) ($existing['outcome'] ?? 'no_win'),
                'code' => (string) ($existing['code'] ?? ''),
                'discount_percent' => (int) ($existing['discount_percent'] ?? 0),
                'valid_until' => (string) ($existing['valid_until'] ?? ''),
            ];
        }

        $percent = $this->drawDiscountPercent();

        $this->pdo->beginTransaction();
        try {
            $codeId = null;
            $code = '';
            $validUntil = '';
            $code = $this->generateCode();
            $validUntil = gmdate('Y-m-d H:i:s', time() + 14 * 86400);
            $insCode = $this->pdo->prepare(
                "INSERT INTO equipment_discount_code
                 (code, user_id, discount_percent, status, generated_from, valid_from, valid_until)
                 VALUES (:c, :u, :p, 'active', 'lucky_spin', UTC_TIMESTAMP(), :vu)"
            );
            $insCode->execute([
                ':c' => $code,
                ':u' => $userId,
                ':p' => $percent,
                ':vu' => $validUntil,
            ]);
            $codeId = (int) $this->pdo->lastInsertId();

            $insSpin = $this->pdo->prepare(
                "INSERT INTO equipment_lucky_spin (user_id, spin_date, outcome, discount_code_id)
                 VALUES (:u, CURDATE(), :o, :cid)"
            );
            $insSpin->execute([
                ':u' => $userId,
                ':o' => 'discount',
                ':cid' => $codeId,
            ]);
            $this->pdo->commit();

            return [
                'already_spun' => false,
                'outcome' => 'discount',
                'code' => $code,
                'discount_percent' => $percent,
                'valid_until' => $validUntil,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function validateCodeForUser(int $userId, string $code): ?array
    {
        $clean = strtoupper(trim($code));
        if ($clean === '') {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT id, code, user_id, discount_percent, status, valid_from, valid_until
             FROM equipment_discount_code
             WHERE code = :c
               AND user_id = :u
               AND status = 'active'
               AND valid_from <= UTC_TIMESTAMP()
               AND valid_until >= UTC_TIMESTAMP()
             LIMIT 1"
        );
        $stmt->execute([':c' => $clean, ':u' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function markCodeUsed(int $codeId, int $reservationId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE equipment_discount_code
             SET status = 'used', used_at = UTC_TIMESTAMP(), used_reservation_id = :r
             WHERE id = :id AND status = 'active'"
        );
        $stmt->execute([':id' => $codeId, ':r' => $reservationId]);

        return $stmt->rowCount() > 0;
    }

    private function drawDiscountPercent(): int
    {
        $r = random_int(1, 100);
        if ($r <= 45) {
            return 5;
        }
        if ($r <= 80) {
            return 10;
        }
        if ($r <= 95) {
            return 15;
        }

        return 20;
    }

    private function generateCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = 'CZ-LUCKY-' . strtoupper(bin2hex(random_bytes(3)));
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM equipment_discount_code WHERE code = :c');
            $stmt->execute([':c' => $code]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $code;
            }
        }

        return 'CZ-LUCKY-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
