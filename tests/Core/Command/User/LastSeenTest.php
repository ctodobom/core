<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Tests\Core\Command\User;


use OC\Core\Command\User\LastSeen;
use Test\TestCase;

class LastSeenTest extends TestCase {
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $userManager;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $connection;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $consoleInput;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $consoleOutput;

	/** @var \Symfony\Component\Console\Command\Command */
	protected $command;

	protected function setUp() {
		parent::setUp();

		/** @var \OCP\IUserManager $userManager */
		$userManager = $this->userManager = $this->getMockBuilder('OCP\IUserManager')
			->disableOriginalConstructor()
			->getMock();

		/** @var \OCP\IDBConnection $connection */
		$connection = $this->connection = $this->createMock('OCP\IDBConnection');

		$this->consoleInput = $this->createMock('Symfony\Component\Console\Input\InputInterface');
		$this->consoleOutput = $this->createMock('Symfony\Component\Console\Output\OutputInterface');

		$this->command = new LastSeen($userManager, $connection);
	}

	public function validUserLastSeen() {
		return [
			[0, 'never logged in'],
			[time(), 'last login'],
		];
	}

	/**
	 * @dataProvider validUserLastSeen
	 *
	 * @param int $lastSeen
	 * @param string $expectedString
	 */
	public function testValidUser($lastSeen, $expectedString) {
		$user = $this->createMock('OCP\IUser');
		$user->expects($this->once())
			->method('getLastLogin')
			->willReturn($lastSeen);

		$queryBuilder = $this->createMock('OCP\DB\QueryBuilder\IQueryBuilder');

		$this->connection->expects($this->once())
			->method('getQueryBuilder')
			->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->at(0))
			->method('select')
			->with(['userid', 'configvalue'])
			->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->at(1))
			->method('from')
			->with('preferences')
			->will($this->returnValue($queryBuilder));
		$queryBuilder->expects($this->at(2))
			->method('where')
			->will($this->returnValue($queryBuilder));
		$queryBuilder->expects($this->at(3))
			->method('andWhere')
			->will($this->returnValue($queryBuilder));
		$queryBuilder->expects($this->at(4))
			->method('orderBy')
			->with('configvalue', 'DESC')
			->will($this->returnValue($queryBuilder));

		$queryBuilder->expects($this->at(5))
			->method('andWhere')
			// userid
			->will($this->returnValue($queryBuilder));
		$queryBuilder->expects($this->at(6))
			->method('setMaxResults')
			->with(10)
			->will($this->returnValue($queryBuilder));

		$queryBuilder->expects($this->at(7))
			->method('execute')
			->will($this->returnValue(['userid' => 'user', 'lastLogin' => $lastSeen]));

		$this->userManager->expects($this->once())
			->method('get')
			->with('user')
			->willReturn($user);

		$this->consoleInput->expects($this->once())
			->method('getArgument')
			->with('uid')
			->willReturn('user');

		$this->consoleOutput->expects($this->once())
			->method('writeln')
			->with($this->stringContains($expectedString));

		self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}

	public function testInvalidUser() {
		$this->userManager->expects($this->once())
			->method('get')
			->with('user')
			->willReturn(null);

		$this->consoleInput->expects($this->once())
			->method('getArgument')
			->with('uid')
			->willReturn('user');

		$this->consoleOutput->expects($this->once())
			->method('writeln')
			->with($this->stringContains('User does not exist'));

		self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}
}
