<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */
require_once 'globals.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode(
    [
        'title' => 'Dynamic Mess',
        'description' => "Un autre blog sur l'histoire et les techniques",
        'copyrightMessage' => 'Copyright Dynamic Mess 2011-2022',
        'copyrightExtraMessage' => '2005 - 2022 Powered by Zend Technologies Ltd (PHP and Zend Framework 2). All rights reserved.',
        'linkedin' => 'ecourtial',
        'github' => 'ecourtial',
        'googleAnalytics' => 'UA-169667453-1'
    ]
);
