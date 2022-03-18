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
    . ' ORDER BY articles.DATE DESC LIMIT 30';

$query = $connection->prepare($sqlRequest);
$query->execute();

$feedContent = '<?xml version="1.0" encoding="utf-8"?>';
$feedContent .= PHP_EOL . '<rss version="2.0">';
$feedContent .= PHP_EOL . "\t" . '<channel>';
$feedContent .= PHP_EOL . "\t\t" . '<title>Dynamic-Mess RSS Feed</title>';

while ($post = $query->fetch()) {
    if (true === (bool)$post['isOnline']) {
        $feedContent .= PHP_EOL . "\t\t" . '<item>';
        $feedContent .= PHP_EOL . "\t\t\t" . '<title>' . cleanAccents($post['postTitle']) . '</title>';
        $feedContent .= PHP_EOL . "\t\t\t" . '<description>' . cleanAccents($post['postDescription']) . '</description>';
        $feedContent .= PHP_EOL . "\t\t\t" . '<link>' . $frontUrl . $post['categURL'] . '/' . $post['postURL'] . '/</link>';
        $feedContent .= PHP_EOL . "\t\t\t" . '<pubDate>' . $post['postDate'] . '</pubDate>';
        $feedContent .= PHP_EOL . "\t\t" .'</item>';
    }
}

$query->closeCursor();

$feedContent .= PHP_EOL . "\t" . '</channel>';
$feedContent .= PHP_EOL . '</rss>';

header('Content-Type: text/xml; charset=utf-8');
echo $feedContent;
exit;
