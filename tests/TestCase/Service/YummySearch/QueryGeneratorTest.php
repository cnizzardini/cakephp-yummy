<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Yummy\Service\YummySearch\QueryGenerator;

class QueryGeneratorTest extends TestCase
{
    public function testGetWhere()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.name',
            'eq',
            'NY Giants'
        );

        $results = $query->toArray();

        $this->assertCount(1, $results);

        $row = reset($results);

        $this->assertEquals('NY Giants', $row->name);
    }

    public function testGetWhereBaseModelSingleNotEqualCondition()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.id',
            'not_eq',
            '1'
        );

        $results = $query->toArray();

        $this->assertCount(31, $results);
    }

    public function testGetWhereBaseModelSingleLikeCondition()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.name',
            'like',
            'NY'
        );

        $results = $query->toArray();

        $this->assertCount(2, $results);

        $row = reset($results);
        $this->assertEquals('NY Giants', $row->name);

        $row = end($results);
        $this->assertEquals('NY Jets', $row->name);
    }

    public function testGetWhereBaseModelSingleNotLikeCondition()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.name',
            'not_like',
            'NY G'
        );

        $results = $query->toArray();

        $this->assertCount(31, $results);
    }

    public function testGetWhereBaseModelSingleGreaterThanCondition()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.id',
            'gt',
            '1'
        );

        $results = $query->toArray();

        $this->assertCount(31, $results);
    }

    public function testGetWhereBaseModelSingleLessThanCondition()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.id',
            'lt',
            '10'
        );

        $results = $query->toArray();

        $this->assertCount(9, $results);
    }
}