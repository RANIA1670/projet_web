-- Exemples de demandes (schéma user_id / purpose / pending)
USE cityzen;

INSERT INTO reservation (equipment_id, user_id, start_date, end_date, purpose, status) VALUES
(1, 101, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 'Travaux voirie — tranchée', 'pending'),
(4, 42, DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'Tournée inspection', 'pending');
