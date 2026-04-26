<?php
/**
 * CityZen - Routes Definition
 */

// Home
$router->get('/', 'HomeController', 'index');
$router->get('/accueil', 'HomeController', 'index');

// Signalements
$router->get('/signalements', 'SignalementController', 'index');
$router->get('/signalement/creer', 'SignalementController', 'create');
$router->post('/signalement/creer', 'SignalementController', 'store');
$router->get('/signalement/{id}', 'SignalementController', 'show');
$router->get('/signalement/{id}/modifier', 'SignalementController', 'edit');
$router->post('/signalement/{id}/modifier', 'SignalementController', 'update');
$router->post('/signalement/{id}/supprimer', 'SignalementController', 'destroy');

// API AJAX pour signalements
$router->get('/api/signalements', 'SignalementController', 'apiList');
$router->get('/api/stats', 'SignalementController', 'apiStats');

// Interventions - Demandes
$router->get('/intervention/demande', 'InterventionController', 'demandeForm');
$router->post('/intervention/demande', 'InterventionController', 'storeDemande');
$router->get('/interventions', 'InterventionController', 'index');
$router->get('/intervention/{id}', 'InterventionController', 'show');

// Suivi
$router->get('/suivi', 'SuiviController', 'index');
$router->get('/suivi/{id}', 'SuiviController', 'show');
$router->post('/suivi/{id}/commentaire', 'SuiviController', 'addComment');
$router->get('/api/suivi/{id}', 'SuiviController', 'apiGet');

// Contact
$router->get('/contact', 'ContactController', 'index');
$router->post('/contact', 'ContactController', 'send');

// Auth
$router->get('/auth/connexion', 'AuthController', 'loginForm');
$router->post('/auth/connexion', 'AuthController', 'login');
$router->get('/auth/inscription', 'AuthController', 'registerForm');
$router->post('/auth/inscription', 'AuthController', 'register');
$router->get('/auth/deconnexion', 'AuthController', 'logout');

// Administration
$router->get('/admin', 'AdminController', 'index');
$router->get('/admin/signalements', 'AdminController', 'signalements');
$router->get('/admin/interventions', 'AdminController', 'interventions');
$router->post('/admin/intervention/{id}/assigner', 'AdminController', 'assignTechnicien');
$router->post('/admin/intervention/{id}/statut', 'AdminController', 'updateInterventionStatus');
$router->post('/admin/intervention/{id}/supprimer', 'AdminController', 'deleteIntervention');
$router->get('/admin/techniciens', 'AdminController', 'techniciens');
