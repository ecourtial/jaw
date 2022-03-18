<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once 'src/globals.php';

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
    . ' WHERE articles.CATEG = categories.ID'
    . ' AND articles.HOME = 1 ORDER BY articles.DATE DESC LIMIT 10';

$query = $connection->prepare($sqlRequest);
$query->execute();

$posts = [];

while ($post = $query->fetch()) {
    if (true === (bool)$post['isOnline']) {
        $posts[] = [
            'id'          => $post['postId'],
            'published' => $post['postDate'],
            'title'       => cleanAccents($post['postTitle']),
            'url'         => $post['categURL'] . '/' . $post['postURL'],
            'description' => cleanAccents($post['postDescription']),
            'highlighted' => (bool)$post['isHighlighted'],
            'obsolete'    => (bool)$post['isObsolete'],
            'indexed'     => true,
            'categoryId' => $post['categId'],
            'categoryName' => cleanAccents($post['categName']),
            'categoryUrl' => $post['categURL'],
        ];
    }
}

$query->closeCursor();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($posts);
exit;
