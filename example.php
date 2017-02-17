<?php

$peoples = [
    [
        'name'=>'Alice',
        'id' => 1,
    ],
    [
        'name'=>'Bob',
        'id' => 2,
    ]
];

$reviews = [
    [
        'vote_rate'=>5,
        'id' => 1,
        'vote_date' => '2001-11-17 14:23:17',
        'user_id' => 1
    ],
    [
        'vote_rate'=>5,
        'id' => 2,
        'vote_date' => '2010-01-01 14:23:17',
        'user_id' => 1
    ],
    [
        'vote_rate'=>4,
        'id' => 3,
        'vote_date' => '2013-02-13 14:23:17',
        'user_id' => 1
    ],
    [
        'vote_rate'=>4,
        'id' => 4,
        'vote_date' => '2017-01-01 14:23:17',
        'user_id' => 2
    ],
    [
        'vote_rate'=>5,
        'id' => 5,
        'vote_date' => '2017-01-17 14:23:17',
        'user_id' => 2
    ],
    [
        'vote_rate'=>4,
        'id' => 6,
        'vote_date' => '2017-01-17 14:23:17',
        'user_id' => 2
    ]
];

include_once 'rating.class.php';

$aliasses = [
    // reviews
    'elem' => 'user_id',
    'rating' => 'vote_rate',
    'date' => 'vote_date',
    // peoples
    'eId' => 'id',
    'eName' => 'name'
];
$oldDays = 90;
$minCount = 3;
$rate = new \Rating($aliasses, $peoples, $reviews, $oldDays, $minCount);

$rates = [
    '0',
    '1',
    '2',
    '3',
    '4',
    '5',
    'all',
    'accepted',
    'oldRating',
    'newRating'
];

$result = [];

foreach ($peoples as $key => $row) {
    foreach ($rates as $rateName){
        $result[$row['id']][$rateName] = $rate->getCount($row['id'],$rateName);
    }
}

print_r($result);