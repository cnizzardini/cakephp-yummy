<?php
namespace Yummy\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DivisionsFixture
 *
 */
class DivisionsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'smallinteger', 'length' => 1, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'conference_id' => ['type' => 'smallinteger', 'length' => 2, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 8, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'latin1_swedish_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Init method
     *
     * @return void
     */
    public function init() : void
    {
        $this->records = [
            [
                'id' => 1,
                'conference_id' => 1,
                'name' => 'East'
            ],
            [
                'id' => 2,
                'conference_id' => 1,
                'name' => 'West'
            ],
            [
                'id' => 3,
                'conference_id' => 1,
                'name' => 'North'
            ],
            [
                'id' => 4,
                'conference_id' => 1,
                'name' => 'South'
            ],
            [
                'id' => 5,
                'conference_id' => 2,
                'name' => 'East'
            ],
            [
                'id' => 6,
                'conference_id' => 2,
                'name' => 'West'
            ],
            [
                'id' => 7,
                'conference_id' => 2,
                'name' => 'North'
            ],
            [
                'id' => 8,
                'conference_id' => 2,
                'name' => 'South'
            ],
        ];
        parent::init();
    }
}
