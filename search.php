<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once 'src/globals.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 400 Bad request");
    exit;
}

$request = $_POST['request'] ?? '';

if ($request === '') {
    header("HTTP/1.1 400 Bad request: keywords for the search request are missing!");
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
    . ' WHERE articles.TITLE LIKE :request'
    . ' OR articles.CONTENT LIKE :request'
    . ' AND articles.CATEG = categories.ID'
    . ' ORDER BY articles.DATE DESC';


$query = $connection->prepare($sqlRequest);
$bindedRequest = "%$request%";
$query->bindParam('request', $bindedRequest);
$query->execute();

$posts = [];

while ($post = $query->fetch()) {
    if (true === (bool)$post['isOnline']) {
        $posts[] = [
            'id'          => $post['postId'],
            'published' => $post['postDate'],
            'title'       => $post['postTitle'],
            'categoryLabel' => $post['categName'],
            'url'         => $post['categURL'] . '/' . $post['postURL'],
            'description' => $post['postDescription'],
            'highlighted' => (bool)$post['isHighlighted'],
            'obsolete'    => (bool)$post['isObsolete'],
            'indexed'     => true,
        ];
    }
}

$results = [
    'request' => $request,
    'resultCount' => \count($posts),
    'posts' => $posts
];

$query->closeCursor();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($results);
exit;
