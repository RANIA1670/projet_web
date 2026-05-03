<?php
/**
 * ForumController - Contrôleur principal du module Forum
 * CRUD Posts + Replies + gestion des likes.
 */

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Reply.php';
require_once __DIR__ . '/../models/Like.php';

class ForumController
{
    private Post $postModel;
    private Reply $replyModel;
    private Like $likeModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->replyModel = new Reply();
        $this->likeModel = new Like();
    }

    /**
     * CRUD générique: listAll
     * $entity = 'post' | 'reply'
     */
    public function listAll(string $entity = 'post', int $postId = 0): array
    {
        if ($entity === 'reply') {
            if ($postId > 0) {
                return Reply::findByPostId($postId);
            }
            return [];
        }

        return Post::findAll();
    }

    /**
     * CRUD générique: show
     * $entity = 'post' | 'reply'
     */
    public function show(string $entity, int $id)
    {
        if ($entity === 'reply') {
            return Reply::findById($id);
        }

        $post = Post::findById($id);
        if ($post) {
            $post->incrementViewCount();
        }
        return $post;
    }

    /**
     * CRUD générique: create
     * $entity = 'post' | 'reply'
     */
    public function create(string $entity, array $data): bool
    {
        if ($entity === 'reply') {
            $postId = (int)($data['post_id'] ?? 0);
            $userId = (int)($data['user_id'] ?? 0);
            $content = trim((string)($data['content'] ?? ''));
            return $this->createReply($postId, $userId, $content);
        }

        $userId = (int)($data['user_id'] ?? 0);
        $title = trim((string)($data['title'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));
        return $this->createPost($userId, $title, $content);
    }

    /**
     * CRUD générique: update
     * $entity = 'post' | 'reply'
     */
    public function update(string $entity, int $id, array $data): bool
    {
        if ($entity === 'reply') {
            $content = trim((string)($data['content'] ?? ''));
            return $this->updateReply($id, $content);
        }

        $title = trim((string)($data['title'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));
        return $this->updatePost($id, $title, $content);
    }

    /**
     * CRUD générique: delete
     * $entity = 'post' | 'reply'
     */
    public function delete(string $entity, int $id): bool
    {
        if ($entity === 'reply') {
            return $this->deleteReply($id);
        }

        return $this->deletePost($id);
    }

    /**
     * Méthode spécifique de gestion des likes (ajouter/retirer).
     * Si $replyId > 0, le like cible une réponse, sinon un post.
     */
    public function toggleLike(int $userId, int $postId = 0, int $replyId = 0): bool
    {
        if ($userId <= 0) {
            return false;
        }

        if ($replyId > 0) {
            return $this->toggleReplyLike($replyId, $userId);
        }

        if ($postId > 0) {
            return $this->togglePostLike($postId, $userId);
        }

        return false;
    }

    // ====== CRUD Posts ======

    public function listAllPosts(): array
    {
        return $this->listAll('post');
    }

    public function showPost(int $id): ?Post
    {
        $post = $this->show('post', $id);
        return $post instanceof Post ? $post : null;
    }

    public function createPost(int $userId, string $title, string $content): bool
    {
        if ($userId <= 0 || $title === '' || $content === '') {
            return false;
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        $post = new Post($userId, $safeTitle, $safeContent);
        return $post->save();
    }

    public function updatePost(int $postId, string $title, string $content): bool
    {
        $post = Post::findById($postId);
        if (!$post || $title === '' || $content === '') {
            return false;
        }

        $post->setTitle(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));
        $post->setContent(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        return $post->update();
    }

    public function deletePost(int $postId): bool
    {
        $post = Post::findById($postId);
        if (!$post) {
            return false;
        }

        $replies = Reply::findByPostId($postId);
        foreach ($replies as $reply) {
            $likes = Like::findByReplyId($reply->getId());
            foreach ($likes as $like) {
                $like->delete();
            }
            $reply->delete();
        }

        $likes = Like::findByPostId($postId);
        foreach ($likes as $like) {
            $like->delete();
        }

        return $post->delete();
    }

    public function getPostsByUser(int $userId): array
    {
        return Post::findByUserId($userId);
    }

    // ====== CRUD Replies ======

    public function listRepliesByPost(int $postId): array
    {
        return $this->listAll('reply', $postId);
    }

    public function showReply(int $replyId): ?Reply
    {
        $reply = $this->show('reply', $replyId);
        return $reply instanceof Reply ? $reply : null;
    }

    public function createReply(int $postId, int $userId, string $content): bool
    {
        if ($postId <= 0 || $userId <= 0 || $content === '') {
            return false;
        }

        $post = Post::findById($postId);
        if (!$post) {
            return false;
        }

        $reply = new Reply($postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        return $reply->save();
    }

    public function updateReply(int $replyId, string $content): bool
    {
        $reply = Reply::findById($replyId);
        if (!$reply || $content === '') {
            return false;
        }

        $reply->setContent(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        return $reply->update();
    }

    public function deleteReply(int $replyId): bool
    {
        $reply = Reply::findById($replyId);
        if (!$reply) {
            return false;
        }

        $likes = Like::findByReplyId($replyId);
        foreach ($likes as $like) {
            $like->delete();
        }

        return $reply->delete();
    }

    public function getRepliesByUser(int $userId): array
    {
        return Reply::findByUserId($userId);
    }

    public function countRepliesByPost(int $postId): int
    {
        return Reply::countByPostId($postId);
    }

    // ====== Likes ======

    public function togglePostLike(int $postId, int $userId): bool
    {
        if (!Post::findById($postId) || $userId <= 0) {
            return false;
        }

        if (Like::existsByPostIdAndUserId($postId, $userId)) {
            return Like::deleteByPostIdAndUserId($postId, $userId);
        }

        $like = new Like($postId, 0, $userId);
        return $like->save();
    }

    public function toggleReplyLike(int $replyId, int $userId): bool
    {
        if (!Reply::findById($replyId) || $userId <= 0) {
            return false;
        }

        if (Like::existsByReplyIdAndUserId($replyId, $userId)) {
            return Like::deleteByReplyIdAndUserId($replyId, $userId);
        }

        $like = new Like(0, $replyId, $userId);
        return $like->save();
    }

    public function addPostLike(int $postId, int $userId): bool
    {
        if (!Post::findById($postId) || $userId <= 0) {
            return false;
        }

        if (Like::existsByPostIdAndUserId($postId, $userId)) {
            return true;
        }

        $like = new Like($postId, 0, $userId);
        return $like->save();
    }

    public function removePostLike(int $postId, int $userId): bool
    {
        return Like::deleteByPostIdAndUserId($postId, $userId);
    }

    public function addReplyLike(int $replyId, int $userId): bool
    {
        if (!Reply::findById($replyId) || $userId <= 0) {
            return false;
        }

        if (Like::existsByReplyIdAndUserId($replyId, $userId)) {
            return true;
        }

        $like = new Like(0, $replyId, $userId);
        return $like->save();
    }

    public function removeReplyLike(int $replyId, int $userId): bool
    {
        return Like::deleteByReplyIdAndUserId($replyId, $userId);
    }

    public function countPostLikes(int $postId): int
    {
        return Like::countByPostId($postId);
    }

    public function countReplyLikes(int $replyId): int
    {
        return Like::countByReplyId($replyId);
    }

    public function getPostLikes(int $postId): array
    {
        return Like::findByPostId($postId);
    }

    public function getReplyLikes(int $replyId): array
    {
        return Like::findByReplyId($replyId);
    }

    public function hasUserLikedPost(int $postId, int $userId): bool
    {
        return Like::existsByPostIdAndUserId($postId, $userId);
    }

    public function hasUserLikedReply(int $replyId, int $userId): bool
    {
        return Like::existsByReplyIdAndUserId($replyId, $userId);
    }

    // ====== RECHERCHE & FILTRAGE ======

    /**
     * Recherche des posts par mot-clé (titre + contenu).
     *
     * @param string $keyword  Terme à rechercher
     * @param string $sortBy   'created_at' | 'view_count' | 'title'
     * @param string $order    'DESC' | 'ASC'
     * @return array
     */
    public function searchPosts(string $keyword, string $sortBy = 'created_at', string $order = 'DESC'): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return $this->listAllPosts();
        }
        return Post::search($keyword, $sortBy, $order);
    }

    /**
     * Filtre les posts par plage de dates et/ou ordre de tri.
     *
     * @param string|null $dateFrom  Y-m-d ou null
     * @param string|null $dateTo    Y-m-d ou null
     * @param string      $sortBy    Colonne de tri
     * @param string      $order     Direction
     * @return array
     */
    public function filterPosts(
        ?string $dateFrom,
        ?string $dateTo,
        string $sortBy = 'created_at',
        string $order  = 'DESC'
    ): array {
        // Validation simple des dates
        $dateFrom = ($dateFrom && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) ? $dateFrom : null;
        $dateTo   = ($dateTo   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   ? $dateTo   : null;

        return Post::findByDateRange($dateFrom, $dateTo, $sortBy, $order);
    }

    // ====== STATISTIQUES ======

    /**
     * Retourne les statistiques globales du forum.
     *
     * @return array  ['totalPosts', 'totalReplies', 'totalLikes', 'totalViews',
     *                 'monthly', 'topViewed', 'topLiked']
     */
    public function getForumStats(): array
    {
        return Post::getAggregatedStats();
    }
}
