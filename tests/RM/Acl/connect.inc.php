<?php

require __DIR__ . '/../../bootstrap.php';


try {
	$options = Tester\Environment::loadData() + ['user' => NULL, 'password' => NULL];
} catch (Exception $e) {
	Tester\Environment::skip($e->getMessage());
}

try {
	$connection = new Nette\Database\Connection($options['dsn'], $options['user'], $options['password']);
} catch (PDOException $e) {
	Tester\Environment::skip("Connection to '$options[dsn]' failed. Reason: " . $e->getMessage());
}

$driverName = $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
$cacheMemoryStorage = new Nette\Caching\Storages\MemoryStorage;

$structure = new Nette\Database\Structure($connection, $cacheMemoryStorage);
$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
$context = new Nette\Database\Context($connection, $structure, $conventions, $cacheMemoryStorage);

$connect = function ($database) use ($context, $connection) {
	$context->query('DROP DATABASE IF EXISTS ' . $database . ';');
	$context->query('CREATE DATABASE ' . $database . ';');
	$context->query('USE ' . $database . ';');
	Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/structure.sql");
};

$disconnect = function ($database) use ($context) {
	$context->query('DROP DATABASE IF EXISTS ' . $database . ';');
};

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}