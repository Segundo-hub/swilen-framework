<?php

use Swilen\Database\Connection;

uses()->group('Database');

it('Resolve correct Connection instance', function () {
    /**
     * @var \PHPUnit\Framework\TestCase $this
     */
    $connection = new Connection(getMockedPDO(), 'testing', []);

    expect($connection->selectOne('SELECT * FROM testing'))->toBe('all');
    expect($connection->select('SELECT * FROM testing'))->toBe(['TODO' => 'all']);
    expect($connection->statement('SELECT * FROM testing'))->toBeTrue();
    expect($connection->insert('INSERT INTO testing (name) VALUES (9)'))->toBe(1);
});

it('Prepare bindings', function () {
    $db = new Connection(getMockedPDO());

    $time = '2022-02-10 00:00:00';

    $prepared = $db->prepareBindings(['id' => 1, 'time' => new DateTime($time)]);
    expect($prepared)->toBe(['id' => 1, 'time' => $time]);

    $staticTime = strtotime('2022-02-10 10:25:10');

    $prepared = $db->prepareBindings(['id' => 1, 'time' => date('Y-m-d', $staticTime)]);
    expect($prepared)->toBe(['id' => 1, 'time' => '2022-02-10']);

    expect($db->prepareBindings(['active' => true]))->toBe(['active' => 1]);
    expect($db->prepareBindings(['active' => false]))->toBe(['active' => 0]);
});

/**
 * @return \PHPUnit\Framework\MockObject\MockObject|\Swilen\Database\Connection
 */
function getConnectionMock(PDO $pdo = null, $methods = [])
{
    $pdo = $pdo ?: new PDOStub();

    $connection = getMockBuilder(Connection::class)
        ->onlyMethods(array_merge($methods, ['select', 'selectOne', 'partial']))
        ->setConstructorArgs([$pdo])
        ->getMock();

    return $connection;
}

function getMockedPDO()
{
    /**
     * @var \Mockery\MockInterface $query
     */
    $query = Mockery::mock(PDOStatement::class);
    $query->shouldReceive('execute')->with()->andReturn(true);
    $query->shouldReceive('fetchAll')->with()->andReturn(['TODO' => 'all']);
    $query->shouldReceive('fetch')->with()->andReturn('all');
    $query->shouldReceive('setFetchMode')->with(PDO::FETCH_OBJ)->andReturn(true);

    $db = getMockBuilder(PDO::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['prepare', 'exec', 'lastInsertId'])
        ->getMock();

    $db->method('prepare')->willReturn($query);
    $db->method('lastInsertId')->willReturn(1);

    return function () use ($db) {
        return $db;
    };
}

class PDOStub extends PDO
{
    public function __construct()
    {
        // NOTHING
    }
}
