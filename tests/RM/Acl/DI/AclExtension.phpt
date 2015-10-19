<?php

/**
 * Test: AclExtension
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');

$connect($database);
Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/test2.sql");

$configurator = new Nette\Configurator;

$extension = new \RM\Acl\DI\AclExtension;
$extension->register($configurator);

deleteDirectory(__DIR__ . '/../../../temp');
@mkdir(__DIR__ . '/../../../temp');
file_put_contents(__DIR__ . '/../../../temp/config.neon', str_replace("dbname=test", "dbname=$database", file_get_contents(__DIR__ . '/../config.neon')));

$configurator->setTempDirectory(__DIR__ . '/../../../temp');
$configurator->addConfig(__DIR__ . '/../../../temp/config.neon');
$configurator->addConfig(__DIR__ . '/config.acl.neon');

$container = $configurator->createContainer();

Assert::type('RM\Acl\User', $container->getByType('Nette\Security\User'));

$disconnect($database);
