<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once 'globals.php';

$fields = [
    'categories.ID as categId',
    'categories.NAME as categName',
    'categories.URL as categURL',
    'categories.DESCRIPTION as categDescription',
];

$sqlRequest = 'SELECT ' . \implode(', ', $fields)
    . ' FROM categories';

$query = $connection->prepare($sqlRequest);
$query->execute();
$results = [];

while ($result = $query->fetch()) {
    $results[] = [
        'id' => $result['categId'],
        'name' => $result['categName'],
        'url' => $result['categURL'],
        'description' => $result['categDescription'],
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($results);
exit;
