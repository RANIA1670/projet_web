<?php
/**
 * CityZen - Routes Definition
 * Sections séparées : FRONT OFFICE | BACKOFFICE
 */

// ══════════════════════════════════════════════════════
//  FRONT OFFICE  (layout: default)
// ══════════════════════════════════════════════════════

// Accueil
$router->get('/',        'HomeController', 'index');
$router->get('/accueil', 'HomeController', 'index');

// Signalements
$router->get('/signalements',               'SignalementController', 'index');
$router->get('/signalement/creer',          'SignalementController', 'create');
$router->post('/signalement/creer',         'SignalementController', 'store');
$router->get('/signalement/{id}',           'SignalementController', 'show');
$router->get('/signalement/{id}/modifier',  'SignalementController', 'edit');
$router->post('/signalement/{id}/modifier', 'SignalementController', 'update');
$router->post('/signalement/{id}/supprimer','SignalementController', 'destroy');

// API AJAX signalements
$router->get('/api/signalements', 'SignalementController', 'apiList');
$router->get('/api/stats',        'SignalementController', 'apiStats');

// Interventions (front)
$router->get('/intervention/demande',  'InterventionController', 'demandeForm');
$router->post('/intervention/demande', 'InterventionController', 'storeDemande');
$router->get('/interventions',         'InterventionController', 'index');
$router->get('/intervention/{id}',     'InterventionController', 'show');

// Suivi
$router->get('/suivi',                    'SuiviController', 'index');
$router->get('/suivi/{id}',               'SuiviController', 'show');
$router->post('/suivi/{id}/commentaire',  'SuiviController', 'addComment');
$router->get('/api/suivi/{id}',           'SuiviController', 'apiGet');

// Contact
$router->get('/contact',  'ContactController', 'index');
$router->post('/contact', 'ContactController', 'send');

// Authentification Fatma désactivée (comptes = CityZen /controller/).

// ══════════════════════════════════════════════════════
//  BACKOFFICE  (layout: admin — controller: admin/AdminController)
// ══════════════════════════════════════════════════════

// Dashboard
$router->get('/backoffice',            'admin/AdminController', 'dashboard');
$router->get('/backoffice/export-pdf', 'admin/AdminController', 'exportPDF');

// Signalements backoffice
$router->get('/backoffice/signalements', 'admin/AdminController', 'signalements');
$router->get('/backoffice/signalement/{id}', 'admin/AdminController', 'showSignalement');

// Interventions backoffice
$router->get('/backoffice/interventions',                  'admin/AdminController', 'interventions');
$router->get('/backoffice/intervention/creer',             'admin/AdminController', 'createIntervention');
$router->post('/backoffice/intervention/creer',            'admin/AdminController', 'storeIntervention');
$router->get('/backoffice/intervention/{id}',              'admin/AdminController', 'showIntervention');
$router->get('/backoffice/intervention/{id}/edit',         'admin/AdminController', 'editIntervention');
$router->post('/backoffice/intervention/{id}/edit',        'admin/AdminController', 'updateIntervention');
$router->post('/backoffice/intervention/{id}/assigner',    'admin/AdminController', 'assignTechnicien');
$router->post('/backoffice/intervention/{id}/statut',      'admin/AdminController', 'updateInterventionStatus');
$router->post('/backoffice/intervention/{id}/supprimer',   'admin/AdminController', 'deleteIntervention');

// Techniciens backoffice
$router->get('/backoffice/techniciens',               'admin/AdminController', 'techniciens');
$router->get('/backoffice/technicien/creer',          'admin/AdminController', 'createTechnicien');
$router->post('/backoffice/technicien/creer',         'admin/AdminController', 'storeTechnicien');
$router->get('/backoffice/technicien/{id}/edit',      'admin/AdminController', 'editTechnicien');
$router->post('/backoffice/technicien/{id}/edit',     'admin/AdminController', 'updateTechnicien');
$router->post('/backoffice/technicien/{id}/supprimer','admin/AdminController', 'deleteTechnicien');

// ══════════════════════════════════════════════════════
//  CARTOGRAPHIE & NOTIFICATIONS
// ══════════════════════════════════════════════════════

// Carte interactive
$router->get('/carte',                 'MapController', 'index');
$router->get('/api/signalements/carte','MapController', 'getSignalements');
$router->get('/api/zones/stats',       'MapController', 'getStatsByZone');
$router->get('/api/geocode',           'MapController', 'geocode');

// Notifications
$router->get('/notifications',                'NotificationController', 'index');
$router->get('/api/notifications/unread',     'NotificationController', 'getUnread');
$router->get('/api/notifications/widget',     'NotificationController', 'getWidget');
$router->post('/api/notifications/mark-read', 'NotificationController', 'markAsRead');
$router->post('/api/notifications/mark-all',  'NotificationController', 'markAllAsRead');
