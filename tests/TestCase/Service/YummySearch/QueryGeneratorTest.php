<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Yummy\Service\YummySearch\Parameter;
use Yummy\Service\YummySearch\QueryGenerator;

class QueryGeneratorTest extends TestCase
{
    public $fixtures = ['plugin.Yummy.Teams', 'plugin.Yummy.Divisions','plugin.Yummy.Conferences'];

    public function testGetWhere()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Teams','name', $config);
        $parameter->setType('string')->setOperator('eq')->setValue('NY Giants');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(1, $results);

        $row = reset($results);

        $this->assertEquals('NY Giants', $row->name);
    }

    public function testGetWhereBaseModelSingleNotEqualCondition()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Teams','id', $config);
        $parameter->setType('integer')->setOperator('not_eq')->setValue('1');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(31, $results);
    }

    public function testGetWhereBaseModelSingleLikeCondition()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Teams','name', $config);
        $parameter->setType('string')->setOperator('like')->setValue('NY');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(2, $results);

        $row = reset($results);
        $this->assertEquals('NY Giants', $row->name);

        $row = end($results);
        $this->assertEquals('NY Jets', $row->name);
    }

    public function testGetWhereBaseModelSingleNotLikeCondition()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Teams','name', $config);
        $parameter->setType('string')->setOperator('not_like')->setValue('NY G');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(31, $results);
    }

    public function testGetWhereBaseModelSingleGreaterThanCondition()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Teams','id', $config);
        $parameter->setType('integer')->setOperator('gt')->setValue('1');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(31, $results);
    }

    public function testGetWhereBaseModelSingleLessThanCondition()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ])->orderAsc('Teams.name');

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Teams','id', $config);
        $parameter->setType('integer')->setOperator('lt')->setValue('10');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(9, $results);
    }

    public function testCastToDate()
    {
        $query = TableRegistry::getTableLocator()->get('Conferences')->find();

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true,
            'allow' => [
                'Conferences.created' => [
                    'castToDate' => true
                ]
            ]
        ];

        $queryGenerator = new QueryGenerator($config);
        $parameter  = new Parameter('Conferences','created', $config);
        $parameter
            ->setType('datetime')
            ->setOperator('gt_eq')
            ->setValue('2019-01-01 00:00:00');

        $query = $queryGenerator->getWhere($query, $parameter);

        $results = $query->toArray();

        $this->assertCount(2, $results);
    }
}
