<?php

declare(strict_types=1);

/**
 * URLs du forum intégré sous CityZen : /forum/index.php et /admin/forum.php
 * (remplace l’ancienne logique index.php / admin.php à la racine du dépôt amine).
 */

function forum_script_name(): string
{
    return basename(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '')));
}

function forum_project_root(): string
{
    return dirname(__DIR__);
}

function forum_preferred_root_entry(): string
{
    return 'index.php';
}

function forum_front_office_relative_prefix(): string
{
    return '';
}

function forum_is_front_office(): bool
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    return str_contains($script, '/forum/index.php');
}

function forum_public_router(): string
{
    return cityzen_asset('forum/index.php');
}

function forum_build_public_url(string $query): string
{
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

function forum_public_router_file(): string
{
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
    return cityzen_asset('admin/forum.php');
}

function forum_edit_reply_url(int $replyId, int $postId): string
{
    return forum_front_url('page=edit_reply&id=' . $replyId . '&post_id=' . $postId);
}

function forum_delete_reply_url(int $replyId, int $postId): string
{
    return forum_front_url('page=delete_reply&id=' . $replyId . '&post_id=' . $postId);
}
