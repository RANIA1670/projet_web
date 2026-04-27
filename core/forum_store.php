<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return list<array{id:int,name:string,description:?string,posts_count:int}>
 */
function cityzen_forum_categories_with_count(): array
{
    $pdo = cityzen_db();
    $sql = 'SELECT c.id, c.name, c.description, COUNT(p.id) AS posts_count
            FROM forum_categories c
            LEFT JOIN forum_posts p ON p.category_id = c.id
            GROUP BY c.id, c.name, c.description
            ORDER BY c.name ASC';
    $rows = $pdo->query($sql)->fetchAll();

    return array_map(static function (array $r): array {
        return [
            'id' => (int) $r['id'],
            'name' => (string) $r['name'],
            'description' => isset($r['description']) ? (string) $r['description'] : null,
            'posts_count' => (int) $r['posts_count'],
        ];
    }, $rows);
}

/**
 * @return list<array{id:int,title:string,content:string,user_id:int,username:string,category_id:int,category_name:string,created_at:string,replies_count:int}>
 */
function cityzen_forum_posts(?int $categoryId = null, int $limit = 50): array
{
    $pdo = cityzen_db();
    $limit = max(1, min(200, $limit));
    $sql = 'SELECT p.id, p.title, p.content, p.user_id, p.category_id, p.created_at,
                   u.username, c.name AS category_name,
                   (SELECT COUNT(*) FROM forum_replies r WHERE r.post_id = p.id) AS replies_count
            FROM forum_posts p
            INNER JOIN users u ON u.id = p.user_id
            INNER JOIN forum_categories c ON c.id = p.category_id';
    $params = [];
    if ($categoryId !== null && $categoryId > 0) {
        $sql .= ' WHERE p.category_id = :cid';
        $params[':cid'] = $categoryId;
    }
    $sql .= ' ORDER BY p.created_at DESC LIMIT ' . $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * @return array<string, mixed>|null
 */
function cityzen_forum_post_detail(int $postId): ?array
{
    $pdo = cityzen_db();
    $sql = 'SELECT p.id, p.title, p.content, p.user_id, p.category_id, p.created_at,
                   u.username, c.name AS category_name
            FROM forum_posts p
            INNER JOIN users u ON u.id = p.user_id
            INNER JOIN forum_categories c ON c.id = p.category_id
            WHERE p.id = :id
            LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $postId]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

/**
 * @return list<array{id:int,post_id:int,user_id:int,username:string,content:string,parent_reply_id:?int,created_at:string}>
 */
function cityzen_forum_replies(int $postId): array
{
    $pdo = cityzen_db();
    $sql = 'SELECT r.id, r.post_id, r.user_id, r.content, r.parent_reply_id, r.created_at, u.username
            FROM forum_replies r
            INNER JOIN users u ON u.id = r.user_id
            WHERE r.post_id = :pid
            ORDER BY r.created_at ASC, r.id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $postId]);
    return $stmt->fetchAll();
}

function cityzen_forum_create_category(string $name, string $description): array
{
    $name = trim($name);
    $description = trim($description);
    if ($name === '' || mb_strlen($name) > 100) {
        return ['ok' => false, 'error' => 'Nom de catégorie invalide (1-100 caractères).'];
    }
    $pdo = cityzen_db();
    $stmt = $pdo->prepare('INSERT INTO forum_categories (name, description) VALUES (?, ?)');
    try {
        $stmt->execute([$name, $description !== '' ? $description : null]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return ['ok' => false, 'error' => 'Cette catégorie existe déjà.'];
        }
        return ['ok' => false, 'error' => 'Création impossible.'];
    }
    return ['ok' => true];
}

function cityzen_forum_update_category(int $id, string $name, string $description): array
{
    $name = trim($name);
    $description = trim($description);
    if ($id < 1 || $name === '' || mb_strlen($name) > 100) {
        return ['ok' => false, 'error' => 'Données de catégorie invalides.'];
    }
    $pdo = cityzen_db();
    $stmt = $pdo->prepare('UPDATE forum_categories SET name = ?, description = ? WHERE id = ?');
    try {
        $stmt->execute([$name, $description !== '' ? $description : null, $id]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return ['ok' => false, 'error' => 'Nom de catégorie déjà pris.'];
        }
        return ['ok' => false, 'error' => 'Mise à jour impossible.'];
    }
    return ['ok' => true];
}

function cityzen_forum_delete_category(int $id): array
{
    if ($id < 1) {
        return ['ok' => false, 'error' => 'Catégorie invalide.'];
    }
    $pdo = cityzen_db();
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM forum_posts WHERE category_id = ?');
    $cnt->execute([$id]);
    if ((int) $cnt->fetchColumn() > 0) {
        return ['ok' => false, 'error' => 'Impossible de supprimer: des posts sont liés.'];
    }
    $stmt = $pdo->prepare('DELETE FROM forum_categories WHERE id = ?');
    $stmt->execute([$id]);
    return ['ok' => $stmt->rowCount() > 0, 'error' => 'Catégorie introuvable.'];
}

function cityzen_forum_create_post(string $title, string $content, int $userId, int $categoryId): array
{
    $title = trim($title);
    $content = trim($content);
    if ($userId < 1 || $categoryId < 1 || $title === '' || $content === '') {
        return ['ok' => false, 'error' => 'Champs obligatoires manquants.'];
    }
    if (mb_strlen($title) > 200) {
        return ['ok' => false, 'error' => 'Titre trop long (max 200 caractères).'];
    }
    $pdo = cityzen_db();
    $stmt = $pdo->prepare('INSERT INTO forum_posts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$title, $content, $userId, $categoryId]);
    return ['ok' => true, 'id' => (int) $pdo->lastInsertId()];
}

function cityzen_forum_create_reply(int $postId, int $userId, string $content, ?int $parentReplyId = null): array
{
    $content = trim($content);
    if ($postId < 1 || $userId < 1 || $content === '') {
        return ['ok' => false, 'error' => 'Réponse vide ou invalide.'];
    }
    $pdo = cityzen_db();
    if ($parentReplyId !== null && $parentReplyId > 0) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM forum_replies WHERE id = ? AND post_id = ?');
        $check->execute([$parentReplyId, $postId]);
        if ((int) $check->fetchColumn() === 0) {
            return ['ok' => false, 'error' => 'Réponse parent invalide.'];
        }
    } else {
        $parentReplyId = null;
    }
    $stmt = $pdo->prepare('INSERT INTO forum_replies (post_id, user_id, content, parent_reply_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$postId, $userId, $content, $parentReplyId]);
    return ['ok' => true];
}

function cityzen_forum_delete_post_admin(int $id): bool
{
    $pdo = cityzen_db();
    $stmt = $pdo->prepare('DELETE FROM forum_posts WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function cityzen_forum_delete_reply_admin(int $id): bool
{
    $pdo = cityzen_db();
    $stmt = $pdo->prepare('DELETE FROM forum_replies WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}
