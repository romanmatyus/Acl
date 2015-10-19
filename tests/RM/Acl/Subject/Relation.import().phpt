<?php

/**
 * Test: Subject\Relation->import()
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

$subject = new RM\Acl\Subject\Relation($context);

/**
 * Author Isaac Asimov has access to book:* I, robot
 * Customer John Wick has access to book:comment I, robot
 */

$subject->import([
	'resource' => 'book',
	'privilege' => NULL,
	'type' => 'relation',
	'name' => 'author',
	'access' => TRUE,
]);
$subject->import([
	'resource' => 'book',
	'privilege' => 'comment',
	'type' => 'relation',
	'name' => 'customer',
	'access' => TRUE,
]);


Assert::same(
	[
		1 => [
			'id' => 1,
			'resource' => 'book',
			'privilege' => NULL,
			'type' => 'relation',
			'access' => 1,
		],
		[
			'id' => 2,
			'resource' => 'book',
			'privilege' => 'comment',
			'type' => 'relation',
			'access' => 1,
		],
	],
	array_map('toArray', $context->table('acl')->fetchAll())
);

Assert::same(
	[
		[
			'acl_id' => 1,
			'name' => 'author',
		],
		[
			'acl_id' => 2,
			'name' => 'customer',
		],
	],
	array_map('toArray', $context->table('acl_relation')->fetchAll())
);

function toArray ($row) {
	return $row->toArray();
}

$disconnect($database);
