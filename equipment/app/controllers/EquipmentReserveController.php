<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/layout.php';
require_once dirname(__DIR__, 3) . '/model/db.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Equipment.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/Reservation.php';
require_once dirname(__DIR__, 3) . '/equipment/backoffice/app/models/LuckySpin.php';

$pdo = cityzen_db();
$equipment = new Equipment($pdo);
$reservations = new Reservation($pdo);
$luckySpin = new LuckySpin($pdo);

$eqId = (int) ($_GET['equipment_id'] ?? $_POST['equipment_id'] ?? 0);
$extendFrom = (int) ($_GET['extend_from'] ?? $_POST['extend_from'] ?? 0);

$row = $eqId > 0 ? $equipment->findWithType($eqId) : null;
if ($row === null) {
    header('Location: ' . cityzen_asset('equipment/index.php'), true, 302);
    exit;
}

$parent = null;
if ($extendFrom > 0) {
    cityzen_require_citizen_login();
    $parent = $reservations->find($extendFrom);
    if ($parent === null
        || (int) ($parent['user_id'] ?? 0) !== cityzen_current_user_id()
        || (int) ($parent['equipment_id'] ?? 0) !== $eqId
        || ($parent['status'] ?? '') !== 'approved'
    ) {
        header('Location: ' . cityzen_asset('equipment/my-reservations.php'), true, 302);
        exit;
    }
    if (strtotime((string) $parent['end_date']) <= time()) {
        header('Location: ' . cityzen_asset('equipment/my-reservations.php'), true, 302);
        exit;
    }
}

$flash = '';
$flashType = '';
$submittedDiscountCode = trim((string) ($_POST['discount_code'] ?? ''));

/**
 * Nombre de jours facturés (minimum 1) entre deux timestamps.
 */
function equipment_price_days(int $startTs, int $endTs): int
{
    $seconds = max(1, $endTs - $startTs);
    return max(1, (int) ceil($seconds / 86400));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['reserve_submit'])) {
    cityzen_require_citizen_login();
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $flash = 'Session expirée. Rechargez la page.';
        $flashType = 'err';
    } else {
        $uid = cityzen_current_user_id();
        $ext = (int) ($_POST['extend_from'] ?? 0);
        if ($ext > 0) {
            $newEndRaw = (string) ($_POST['end_date'] ?? '');
            $newId = $reservations->createExtensionRequest($ext, $uid, $newEndRaw);
            if ($newId === null) {
                $flash = 'Prolongation impossible (dates ou chevauchement).';
                $flashType = 'err';
            } else {
                header('Location: ' . cityzen_asset('equipment/my-reservations.php?msg=' . rawurlencode('Demande de prolongation envoyée. En attente de validation.') . '&type=ok'), true, 302);
                exit;
            }
        } else {
            $startRaw = (string) ($_POST['start_date'] ?? '');
            $endRaw = (string) ($_POST['end_date'] ?? '');
            $purpose = (string) ($_POST['usage_purpose'] ?? '');
            $discountCodeRaw = strtoupper(trim((string) ($_POST['discount_code'] ?? '')));
            $allowedP = ['event', 'repair', 'inspection'];
            $stEq = (string) $row['status'];
            if ($stEq !== 'available') {
                $flash = 'Cet équipement n\'est pas réservable en ligne.';
                $flashType = 'err';
            } elseif (!in_array($purpose, $allowedP, true)) {
                $flash = 'Choisissez un motif.';
                $flashType = 'err';
            } else {
                $tsS = strtotime($startRaw);
                $tsE = strtotime($endRaw);
                if ($tsS === false || $tsE === false || $tsE <= $tsS || $tsS < time() - 120) {
                    $flash = 'Dates invalides.';
                    $flashType = 'err';
                } elseif ($reservations->hasOverlap($eqId, date('Y-m-d H:i:s', $tsS), date('Y-m-d H:i:s', $tsE), null)) {
                    $flash = 'Ce créneau chevauche une réservation existante.';
                    $flashType = 'err';
                } else {
                    $pricePerDay = (float) ($row['price_per_day'] ?? 0);
                    if ($pricePerDay <= 0) {
                        $pricePerDay = (float) ($row['type_daily_cost'] ?? 0);
                    }
                    $days = equipment_price_days($tsS, $tsE);
                    $subtotal = round($days * max(0, $pricePerDay), 2);
                    $discountPercent = 0;
                    $discountAmount = 0.0;
                    $validatedCode = null;
                    if ($discountCodeRaw !== '') {
                        $validatedCode = $luckySpin->validateCodeForUser($uid, $discountCodeRaw);
                        if ($validatedCode === null) {
                            $flash = 'Code remise invalide, expiré ou déjà utilisé.';
                            $flashType = 'err';
                        } else {
                            $discountPercent = (int) ($validatedCode['discount_percent'] ?? 0);
                            $discountPercent = max(0, min(90, $discountPercent));
                            $discountAmount = round(($subtotal * $discountPercent) / 100, 2);
                        }
                    }
                    if ($flash === '') {
                        $total = max(0.0, round($subtotal - $discountAmount, 2));
                        $pdo->beginTransaction();
                        try {
                            $newId = $reservations->create([
                                'equipment_id'    => $eqId,
                                'user_id'         => $uid,
                                'extension_of_id' => null,
                                'start_date'      => date('Y-m-d H:i:s', $tsS),
                                'end_date'        => date('Y-m-d H:i:s', $tsE),
                                'price_days'      => $days,
                                'price_per_day'   => $pricePerDay,
                                'price_subtotal'  => $subtotal,
                                'discount_code'   => $validatedCode['code'] ?? null,
                                'discount_percent'=> $discountPercent,
                                'discount_amount' => $discountAmount,
                                'price_total'     => $total,
                                'purpose'         => null,
                                'usage_purpose'   => $purpose,
                                'status'          => 'pending',
                            ]);
                            if ($validatedCode !== null) {
                                $luckySpin->markCodeUsed((int) $validatedCode['id'], $newId);
                            }
                            $pdo->commit();
                        } catch (Throwable $e) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            $flash = 'Erreur interne lors de l\'enregistrement de la demande.';
                            $flashType = 'err';
                        }
                    }
                    if ($flash === '') {
                        header('Location: ' . cityzen_asset('equipment/reserve.php?equipment_id=' . $eqId . '&done=1'), true, 302);
                        exit;
                    }
                }
            }
        }
    }
}

$done = isset($_GET['done']);
$eq_title = 'Demande de réservation';
$eq_nav_active = 'equipment';
$busyJson = json_encode($reservations->busyRangesForEquipment($eqId), JSON_UNESCAPED_UNICODE);
$assistantApiUrl = cityzen_asset('api/equipment_suggestions.php');
$luckySpinApiUrl = cityzen_asset('api/equipment_lucky_spin.php');
$pricePerDay = (float) ($row['price_per_day'] ?? 0);
if ($pricePerDay <= 0) {
    $pricePerDay = (float) ($row['type_daily_cost'] ?? 0);
}
