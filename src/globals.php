<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

require_once(__DIR__ . '/../config/.local.env.php');

$connection = new \PDO("mysql:host=$host;dbname=$base", $login, $password, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
