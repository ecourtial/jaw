<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once 'src/globals.php';

$category = $_GET['category'] ?? null;
$slug = $_GET['slug'] ?? null;

if (null === $category || null === $slug) {
    header("HTTP/1.1 400 Invalid request");
    exit;
}

$fields = [
    'categories.ID as categId',
    'categories.NAME as categName',
    'categories.URL as categURL',
    'articles.ID as postId',
    'articles.TITLE as postTitle',
    'articles.URL as postURL',
    'articles.DESCRIPTION as postDescription',
    'articles.DATE as postDate',
    'articles.HOME as isHighlighted',
    'articles.Online as isOnline',
    'articles.Obsolete as isObsolete',
    'articles.CONTENT as content',
];

$sqlRequest = 'SELECT ' . \implode(', ', $fields)
    . ' FROM categories, articles'
    . ' WHERE categories.URL = :categSlug AND articles.URL = :articleSlug'
    . ' AND articles.CATEG = categories.ID LIMIT 1';

$query = $connection->prepare($sqlRequest);
$query->bindParam('categSlug', $category);
$query->bindParam('articleSlug', $slug);
$query->execute();
$result = $query->fetchAll();
$query->closeCursor();

if (false === is_array($result) || [] === $result) {
    header("HTTP/1.1 404 Not Found");
} else {
    $result = $result[0];
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'id' => $result['postId'],
        'title' => cleanAccents($result['postTitle']),
        'description' => cleanAccents($result['postDescription']),
        'published' => $result['postDate'],
        'categoryId' => $result['categId'],
        'categoryUrl' => $result['categURL'],
        'categoryLabel' => cleanAccents($result['categName']),
        'url' => $result['categURL'] . '/' . $result['postURL'],
        'online' => (bool)$result['isOnline'],
        'highlighted' => (bool)$result['isHighlighted'],
        'obsolete' => (bool)$result['isObsolete'],
        'indexed' => true,
        'content' => $result['content']
    ]);
}

exit;
