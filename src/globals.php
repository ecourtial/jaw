<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

require_once(__DIR__ . '/../config/.local.env.php');

$connection = new \PDO("mysql:host=$host;dbname=$base", $login, $password, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$frontUrl = 'https://www.dynamic-mess.com/';

// Accents

$charMap = array_flip([
                'à' => 'Ã',
                'â' => 'Ã¢',
                'é' => 'Ã©',
                'è' => 'Ã¨',
                'ê' => 'Ãª',
                'ë' => 'Ã«',
                'î' => 'Ã®',
                'ï' => 'Ã¯',
                'ô' => 'Ã´',
                'ö' => 'Ã¶',
                'ù' => 'Ã¹',
                'û' => 'Ã»',
                'ü' => 'Ã¼',
                'ç' => 'Ã§',
                'œ' => 'Å',
                '€' => 'â',
                '°' => 'Â°',
                // 'À' => 'Ã',
                // 'Â' => 'Ã',
                // 'É' => 'Ã',
                // 'È' => 'Ã',
                // 'Ê' => 'Ã',
                // 'Ë' => 'Ã',
                // 'Î' => 'Ã',
                // 'Ï' => 'Ã',
                // 'Ô' => 'Ã',
                // 'Ö' => 'Ã',
                // 'Ù' => 'Ã',
                // 'Û' => 'Ã',
                // 'Ü' => 'Ã',
                // 'Ç' => 'Ã',
                // 'Œ' => 'Å'
            ]);

function cleanAccents(string $stringToClean): string
{
    //return $stringToClean;

    global $charMap;

    $data = strtr($stringToClean, $charMap);
    $data = str_replace('€€™', "'", $data);

    return $data;
}
