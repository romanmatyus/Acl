<?php

/**
 * Test: Subject\Relation->isAllowed()
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

$database = 'test_' . Nette\Utils\Random::generate(10, 'a-zA-Z');
$connect($database);

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/test1.sql");

$subject = new RM\Acl\Subject\Relation($context);

/**
 * @resourceName book
 * @relationship (author, customer)
 */
class Book extends Nette\Object implements RM\Acl\IRelation
{
	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function verifyRelation($user, $relation, $id)
	{
		$book = $this->db->table('book')->get($id);
		if ($book instanceof Nette\Database\Table\ActiveRow) {
			switch ($relation) {
				case 'author':
					return $book->author === $user->id;
				case 'customer':
					return (bool) $book->related('user_book')->where('user_id', $user->id)->fetch();
			}
		}
	}
}

$book = new Book($context);
$subject->addResource('book', ['author', 'customer'], [$book, 'verifyRelation']);

/**
 * Author Isaac Asimov has access to book:* I, robot
 */

$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getId')->andReturn(3) // Isaac Asimov
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', NULL, 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', NULL, 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 2));

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

/**
 * Author Arthur C. Clarke has access to book:* 2001: A Space Odyssey
 */

$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getId')->andReturn(4) // Arthur C. Clarke
	->getMock();

Assert::same(NULL, $subject->isAllowed($user, 'book'));
Assert::same(NULL, $subject->isAllowed($user, 'book', NULL, 1)); // I, robot
Assert::same(TRUE, $subject->isAllowed($user, 'book', NULL, 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'detail', 1)); // I, robot
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'detail', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'publish', 1)); // I, robot
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'publish', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment'));
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));


/**
 * Customer John Doe has access to book:comment I, robot and 2001: A Space Odyssey
 */

$user = Mockery::mock('Nette\Security\User')
	->shouldReceive('getId')->andReturn(1) // John Doe
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
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));

/**
 * Customer John Wick has access to book:comment I, robot
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
Assert::same(TRUE, $subject->isAllowed($user, 'book', 'comment', 1)); // I, robot
Assert::same(NULL, $subject->isAllowed($user, 'book', 'comment', 2)); // 2001: A Space Odyssey

Assert::same(NULL, $subject->isAllowed($user, 'book', 'undefined'));
Assert::same(NULL, $subject->isAllowed($user, 'undefined', 'undefined'));


$disconnect($database);
