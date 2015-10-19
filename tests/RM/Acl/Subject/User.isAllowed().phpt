<?php

/**
 * Test: Subject\User->asAllowed()
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/test1.sql");

$subject = new RM\Acl\Subject\User($context);

/**
 * User Arthur C. Clarke has not access to book:comment 2001: I, robot
 */

$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getId')->andReturn(4) // Arthur C. Clarke
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));
Assert::same(NULL, $subject->isAllowed($user, 'book', NULL, 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', NULL, 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(FALSE, $subject->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

/**
 * User John Wick has access to book:comment 2001: A Space Odyssey
 */
$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getId')->andReturn(2) // John Wick
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));
Assert::same(NULL, $subject->isAllowed($user, 'book', NULL, 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', NULL, 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

$disconnect($database);
