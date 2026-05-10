<?php
/**
 * URLs du forum et du back-office selon le script courant (évite les liens cassés).
 *
 * - index.php à la racine : rester sur index.php (pas ../../front.php).
 * - views/front_office/app.php : les liens publics pointent vers ../../index.php (ou front.php).
 */

function forum_script_name(): string
{
    return basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
}

function forum_project_root(): string
{
    return dirname(__DIR__);
}

/** Fichier d’entrée public à la racine du projet (index.php prioritaire si présent). */
function forum_preferred_root_entry(): string
{
    $root = forum_project_root();

    return is_file($root . DIRECTORY_SEPARATOR . 'index.php') ? 'index.php' : 'front.php';
}

/**
 * Préfixe relatif vers la racine du forum quand le script courant est app.php dans views/front_office/.
 */
function forum_front_office_relative_prefix(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if (str_contains($script, '/views/front_office/') && basename($script) === 'app.php') {
        return '../../';
    }

    return '';
}

/**
 * True sur les interfaces publiques du forum (pas l’admin / back-office).
 * Like et épinglage : à n’afficher que lorsque cette fonction est vraie.
 */
function forum_is_front_office(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $name    = basename($script);

    if ($name === 'admin.php') {
        return false;
    }
    if ($name === 'index.php' && str_contains($script, 'back_office')) {
        return false;
    }

    return $name === 'index.php'
        || $name === 'front.php'
        || $name === 'app.php';
}

/**
 * Nom du script routeur public courant (sans remonter depuis app.php).
 */
function forum_public_router(): string
{
    $root   = forum_project_root();
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $name   = basename($script);

    if ($name === 'front.php') {
        return 'front.php';
    }
    if ($name === 'app.php') {
        return 'app.php';
    }
    if ($name === 'index.php' && str_contains($script, 'back_office')) {
        return is_file($root . DIRECTORY_SEPARATOR . 'front.php') ? '../../front.php' : '../../index.php';
    }
    if ($name === 'index.php') {
        return 'index.php';
    }
    if ($name === 'admin.php') {
        return is_file($root . DIRECTORY_SEPARATOR . 'front.php') ? 'front.php' : 'index.php';
    }

    if (is_file($root . DIRECTORY_SEPARATOR . 'front.php')) {
        return 'front.php';
    }

    return 'index.php';
}

/** URL publique complète (query sans ? initial géré). */
function forum_build_public_url(string $query): string
{
    $nest = forum_front_office_relative_prefix();
    if ($nest !== '') {
        return $nest . forum_preferred_root_entry() . '?' . $query;
    }

    return forum_public_router() . '?' . $query;
}

function forum_list_url(string $query = 'page=home'): string
{
    return forum_build_public_url($query);
}

function forum_front_url(string $query): string
{
    return forum_build_public_url($query);
}

/**
 * Script public sans query — pour formulaires GET (action + champs page, q, …).
 */
function forum_public_router_file(): string
{
    $nest = forum_front_office_relative_prefix();
    if ($nest !== '') {
        return $nest . forum_preferred_root_entry();
    }

    return forum_public_router();
}

function forum_post_url(int $postId): string
{
    return forum_front_url('page=post&id=' . $postId);
}

function forum_edit_post_url(int $postId): string
{
    return forum_front_url('page=edit&id=' . $postId);
}

function forum_delete_post_url(int $postId): string
{
    return forum_front_url('page=delete&id=' . $postId);
}

function forum_admin_nav_base(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $name    = basename($script);
    if ($name === 'admin.php') {
        return 'admin.php';
    }
    if ($name === 'index.php' && str_contains($script, 'back_office')) {
        return 'index.php';
    }

    return 'admin.php';
}

function forum_edit_reply_url(int $replyId, int $postId): string
{
    return forum_front_url('page=edit_reply&id=' . $replyId . '&post_id=' . $postId);
}

function forum_delete_reply_url(int $replyId, int $postId): string
{
    return forum_front_url('page=delete_reply&id=' . $replyId . '&post_id=' . $postId);
}
