<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once 'src/globals.php';

$category = $_GET['category'] ?? null;

if (null === $category) {
    header("HTTP/1.1 400 Invalid request");
    exit;
}

$fields = [
    'categories.ID as categId',
    'categories.NAME as categName',
    'categories.URL as categURL',
    'categories.DESCRIPTION as categDescription',
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
    . ' WHERE categories.URL = :categSlug'
    . ' AND articles.CATEG = categories.ID';

$query = $connection->prepare($sqlRequest);
$query->bindParam('categSlug', $category);
$query->execute();

$posts = [];
$categId = 0;
$categName = '';
$categDescription = '';

while ($post = $query->fetch()) {
    $categId = $post['categId'];
    $categName = $post['categName'];
    $categDescription = $post['categDescription'];

    if (true === (bool)$post['isOnline']) {
        $posts[] = [
            'id'          => $post['postId'],
            'title'       => $post['postTitle'],
            'url'         => $post['categURL'] . '/' . $post['postURL'],
            'description' => $post['postDescription'],
            'highlighted' => (bool)$post['isHighlighted'],
            'obsolete'    => (bool)$post['isObsolete'],
            'indexed'     => true,
            'published' => $post['postDate'],
        ];
    }
}

if (0 === $categId) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

$results = [
    'id' => $categId,
    'name' => $categName,
    'url' => $category,
    'description' => $categDescription,
    'posts' => $posts
];

$query->closeCursor();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($results);
exit;
