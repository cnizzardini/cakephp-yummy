<?php
namespace Yummy\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TeamsFixture
 *
 */
class TeamsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'smallinteger', 'length' => 2, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'division_id' => ['type' => 'smallinteger', 'length' => 2, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 32, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'abbreviation' => ['type' => 'string', 'fixed' => true, 'length' => 3, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
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
    public function init()
    {
        $this->records = [
            [
                'id' => 1,
                'division_id' => 6,
                'name' => 'Arizona Cardinals',
                'abbreviation' => 'ARI'
            ],
            [
                'id' => 2,
                'division_id' => 8,
                'name' => 'Atlanta Falcons',
                'abbreviation' => 'ATL'
            ],
            [
                'id' => 3,
                'division_id' => 3,
                'name' => 'Baltimore Ravens',
                'abbreviation' => 'BAL'
            ],
            [
                'id' => 4,
                'division_id' => 1,
                'name' => 'Buffalo Bills',
                'abbreviation' => 'BUF'
            ],
            [
                'id' => 5,
                'division_id' => 8,
                'name' => 'Carolina Panthers',
                'abbreviation' => 'CAR'
            ],
            [
                'id' => 6,
                'division_id' => 7,
                'name' => 'Chicago Bears',
                'abbreviation' => 'CHI'
            ],
            [
                'id' => 7,
                'division_id' => 3,
                'name' => 'Cincinnati Bengals',
                'abbreviation' => 'CIN'
            ],
            [
                'id' => 8,
                'division_id' => 3,
                'name' => 'Cleveland Browns',
                'abbreviation' => 'CLE'
            ],
            [
                'id' => 9,
                'division_id' => 5,
                'name' => 'Dallas Cowboys',
                'abbreviation' => 'DAL'
            ],
            [
                'id' => 10,
                'division_id' => 2,
                'name' => 'Denver Broncos',
                'abbreviation' => 'DEN'
            ],
            [
                'id' => 11,
                'division_id' => 7,
                'name' => 'Detroit Lions',
                'abbreviation' => 'DET'
            ],
            [
                'id' => 12,
                'division_id' => 7,
                'name' => 'Green Bay Packers',
                'abbreviation' => 'GB'
            ],
            [
                'id' => 13,
                'division_id' => 4,
                'name' => 'Houston Texans',
                'abbreviation' => 'HOU'
            ],
            [
                'id' => 14,
                'division_id' => 4,
                'name' => 'Indianapolis Colts',
                'abbreviation' => 'IND'
            ],
            [
                'id' => 15,
                'division_id' => 4,
                'name' => 'Jacksonville Jaguars',
                'abbreviation' => 'JAX'
            ],
            [
                'id' => 16,
                'division_id' => 2,
                'name' => 'Kansas City Chiefs',
                'abbreviation' => 'KC'
            ],
            [
                'id' => 17,
                'division_id' => 1,
                'name' => 'Miami Dolphins',
                'abbreviation' => 'MIA'
            ],
            [
                'id' => 18,
                'division_id' => 7,
                'name' => 'Minnesota Vikings',
                'abbreviation' => 'MIN'
            ],
            [
                'id' => 19,
                'division_id' => 1,
                'name' => 'New England Patriots',
                'abbreviation' => 'NE'
            ],
            [
                'id' => 20,
                'division_id' => 8,
                'name' => 'New Orleans Saints',
                'abbreviation' => 'NO'
            ],
            [
                'id' => 21,
                'division_id' => 5,
                'name' => 'NY Giants',
                'abbreviation' => 'NYG'
            ],
            [
                'id' => 22,
                'division_id' => 1,
                'name' => 'NY Jets',
                'abbreviation' => 'NYJ'
            ],
            [
                'id' => 23,
                'division_id' => 2,
                'name' => 'Oakland Raiders',
                'abbreviation' => 'OAK'
            ],
            [
                'id' => 24,
                'division_id' => 5,
                'name' => 'Philadelphia Eagles',
                'abbreviation' => 'PHI'
            ],
            [
                'id' => 25,
                'division_id' => 3,
                'name' => 'Pittsburgh Steelers',
                'abbreviation' => 'PIT'
            ],
            [
                'id' => 26,
                'division_id' => 2,
                'name' => 'San Diego Chargers',
                'abbreviation' => 'SD'
            ],
            [
                'id' => 27,
                'division_id' => 6,
                'name' => 'San Francisco 49ers',
                'abbreviation' => 'SF'
            ],
            [
                'id' => 28,
                'division_id' => 6,
                'name' => 'Seattle Seahawks',
                'abbreviation' => 'SEA'
            ],
            [
                'id' => 29,
                'division_id' => 6,
                'name' => 'St. Louis Rams',
                'abbreviation' => 'STL'
            ],
            [
                'id' => 30,
                'division_id' => 8,
                'name' => 'Tampa Bay Buccaneers',
                'abbreviation' => 'TB'
            ],
            [
                'id' => 31,
                'division_id' => 4,
                'name' => 'Tennessee Titans',
                'abbreviation' => 'TEN'
            ],
            [
                'id' => 32,
                'division_id' => 5,
                'name' => 'Washington Redskins',
                'abbreviation' => 'WAS'
            ],
        ];
        parent::init();
    }
}
