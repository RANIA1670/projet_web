<?php
// ================================================
//  FICHIER  : models/Model.php
//  RÔLE     : Classe parente de tous les modèles
//             Fournit l'accès PDO à toutes les classes filles
// ================================================

require_once __DIR__ . '/Database.php';

abstract class Model
{
    // La connexion PDO partagée par tous les modèles
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }
}
