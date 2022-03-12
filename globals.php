<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

$host = 'localhost';
$base = '';
$login = '';
$password = '';

$connection = new \PDO("mysql:host=$host;dbname=$base", $login, $password);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

