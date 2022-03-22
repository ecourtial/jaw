<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once '../config/globals.php';

$fields = [
    'categories.ID as categId',
    'categories.NAME as categName',
    'categories.URL as categURL',
    'categories.DESCRIPTION as categDescription',
];

$sqlRequest = 'SELECT ' . \implode(', ', $fields)
    . ' FROM categories ORDER BY NAME ASC';

$query = $connection->prepare($sqlRequest);
$query->execute();
$results = [];

while ($result = $query->fetch()) {
    $results[] = [
        'id' => $result['categId'],
        'name' => cleanAccents($result['categName']),
        'url' => $result['categURL'],
        'description' => cleanAccents($result['categDescription']),
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($results);
exit;
