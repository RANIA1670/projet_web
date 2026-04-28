<?php

declare(strict_types=1);

/**
 * Traite les POST du back-office. Retourne true si une action a été gérée (redirection incluse).
 */
function bo_handle_post(PDO $pdo): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $action = (string) ($_POST['_action'] ?? '');
    if ($action === '') {
        return false;
    }

    $equipment = new Equipment($pdo);
    $types = new TypeEquipment($pdo);
    $reservations = new Reservation($pdo);

    $redirect = function (string $route, string $msg, string $type = 'success', array $extra = []): void {
        $extra['msg'] = $msg;
        $extra['type'] = $type;
        header('Location: ' . bo_url($route, $extra));
        exit;
    };

    switch ($action) {
        case 'equipment_save':
            $id = !empty($_POST['id']) ? (int) $_POST['id'] : 0;
            $latRaw = $_POST['latitude'] ?? '';
            $lngRaw = $_POST['longitude'] ?? '';
            $data = [
                'name'        => trim((string) ($_POST['name'] ?? '')),
                'status'      => (string) ($_POST['status'] ?? 'available'),
                'location'    => trim((string) ($_POST['location'] ?? '')),
                'type_id'     => (int) ($_POST['type_id'] ?? 0),
                'price_per_day' => max(0, (float) ($_POST['price_per_day'] ?? 0)),
                'last_maintenance' => trim((string) ($_POST['last_maintenance'] ?? '')) ?: null,
                'latitude'    => ($latRaw !== '' && $latRaw !== null) ? (float) $latRaw : null,
                'longitude'   => ($lngRaw !== '' && $lngRaw !== null) ? (float) $lngRaw : null,
            ];
            if ($data['name'] === '' || $data['type_id'] <= 0) {
                $redirect('equipment', 'Champs obligatoires manquants.', 'error');
            }
            if ($data['price_per_day'] <= 0) {
                $typeRow = $types->find($data['type_id']);
                if ($typeRow !== null) {
                    $data['price_per_day'] = max(0, (float) ($typeRow['daily_cost'] ?? 0));
                }
            }
            $allowed = ['available', 'reserved', 'maintenance', 'out_of_service'];
            if (!in_array($data['status'], $allowed, true)) {
                $redirect('equipment', 'Statut invalide.', 'error');
            }
            if ($id > 0) {
                $equipment->update($id, $data);
                $redirect('equipment', 'Équipement mis à jour.');
            }
            $equipment->create($data);
            $redirect('equipment', 'Équipement créé.');

        case 'equipment_delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && $equipment->delete($id)) {
                $redirect('equipment', 'Équipement supprimé.');
            }
            $redirect('equipment', 'Suppression impossible (réservations liées ?).', 'error');

        case 'equipment_bulk_maintenance':
            $ids = $_POST['equipment_ids'] ?? [];
            if (!is_array($ids)) {
                $ids = [];
            }
            $n = $equipment->bulkSetStatus($ids, 'maintenance');
            $redirect('equipment', $n > 0 ? "$n équipement(s) passés en maintenance." : 'Aucun équipement sélectionné.', $n > 0 ? 'success' : 'error');

        case 'type_save':
            $id = !empty($_POST['id']) ? (int) $_POST['id'] : 0;
            $data = [
                'category_name' => trim((string) ($_POST['category_name'] ?? '')),
                'icon'          => trim((string) ($_POST['icon'] ?? '📦')) ?: '📦',
                'daily_cost'    => (float) ($_POST['daily_cost'] ?? 0),
                'warranty_months' => (int) ($_POST['warranty_months'] ?? 12),
                'default_maintenance_frequency_months' => (int) ($_POST['default_maintenance_frequency_months'] ?? 6),
            ];
            if ($data['category_name'] === '') {
                $redirect('types', 'Nom de catégorie requis.', 'error');
            }
            if ($id > 0) {
                $types->update($id, $data);
                $redirect('types', 'Type mis à jour.');
            }
            $types->create($data);
            $redirect('types', 'Type créé.');

        case 'type_delete':
            $id = (int) ($_POST['id'] ?? 0);
            $reassign = (int) ($_POST['reassign_to'] ?? 0);
            if ($id <= 0) {
                $redirect('types', 'ID invalide.', 'error');
            }
            $cnt = $types->countEquipment($id);
            if ($cnt > 0) {
                if ($reassign <= 0 || $reassign === $id) {
                    $redirect('types', 'Réassignez les équipements à un autre type avant suppression.', 'error', ['delete_type' => (string) $id]);
                }
                $types->reassignEquipmentToType($id, $reassign);
            }
            if ($types->delete($id)) {
                $redirect('types', 'Type supprimé.');
            }
            $redirect('types', 'Impossible de supprimer ce type.', 'error');

        case 'reservation_approve':
            $id = (int) ($_POST['reservation_id'] ?? 0);
            $notify = !empty($_POST['send_notification']);
            if ($id <= 0) {
                $redirect('reservations', 'Action invalide.', 'error');
            }
            $row = $reservations->find($id);
            if ($row === null || ($row['status'] ?? '') !== 'pending') {
                $redirect('reservations', 'Demande introuvable ou déjà traitée.', 'error');
            }
            $eqId = (int) $row['equipment_id'];
            $eq = $equipment->find($eqId);
            if ($eq === null || ($eq['status'] ?? '') !== 'available') {
                $redirect('reservations', 'Équipement non disponible.', 'error', ['tab' => 'pending']);
            }
            if ($reservations->hasOverlap($eqId, (string) $row['start_date'], (string) $row['end_date'], $id)) {
                $redirect('reservations', 'Conflit de dates sur cet équipement.', 'error', ['tab' => 'pending']);
            }
            if ($reservations->approve($id, $notify)) {
                $msg = $notify
                    ? 'Approuvé — notification e-mail simulée (aucun envoi SMTP configuré).'
                    : 'Réservation approuvée.';
                $redirect('reservations', $msg, 'success', ['tab' => 'pending']);
            }
            $redirect('reservations', 'Échec approbation.', 'error', ['tab' => 'pending']);

        case 'reservation_reject':
            $id = (int) ($_POST['reservation_id'] ?? 0);
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
            if ($id <= 0 || $reason === '') {
                $redirect('reservations', 'Le motif de refus est obligatoire.', 'error', ['tab' => 'pending']);
            }
            if ($reservations->reject($id, $reason)) {
                $redirect('reservations', 'Demande refusée.', 'success', ['tab' => 'pending']);
            }
            $redirect('reservations', 'Refus impossible.', 'error', ['tab' => 'pending']);

        case 'reservation_return':
            $id = (int) ($_POST['reservation_id'] ?? 0);
            if ($id > 0 && $reservations->markReturned($id)) {
                $redirect('reservations', 'Retour enregistré — équipement disponible.', 'success', ['tab' => 'history']);
            }
            $redirect('reservations', 'Action impossible (réservation non active).', 'error', ['tab' => 'history']);

        case 'reservation_noshow':
            $id = (int) ($_POST['reservation_id'] ?? 0);
            if ($id > 0 && $reservations->markNoShow($id)) {
                $redirect('reservations', 'Marqué comme no-show — équipement disponible.', 'success', ['tab' => 'history']);
            }
            $redirect('reservations', 'Action impossible.', 'error', ['tab' => 'history']);

        default:
            return false;
    }
}
