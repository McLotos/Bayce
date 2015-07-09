<php
require 'ratings.class.php';
 
 $db = new dBase();
 
 $sql = 'SELECT * FROM elems';
 $bankList = $db->select($sql, array());
 
 $sql = 'SELECT `id`,`elem_id`, `vote`, `vote_date` FROM ratings WHERE `acepted`={?}';
 $allRatings = $db->select($sql, array('1'));
 
 $table=array(
                 'elem'=>'elem_id',
                 'rating'=>'vote',
                 'date'=>'vote_date',
                 'eId'=>'id',
                 'eName'=>'name'
              );
                       
 $rating = new Rating($table, $bankList, $allRatings, 90, 10);
 
 //for example I try to get all rating info for element (song, user, page or something else) with id=2
 $test['count']['zeroStars'] = $rating->getCount('2','0');
 $test['count']['oneStars'] = $rating->getCount('2','1');
 $test['count']['twoStars'] = $rating->getCount('2','2');
 $test['count']['threeStars'] = $rating->getCount('2','3');
 $test['count']['fourStars'] = $rating->getCount('2','4');
 $test['count']['fiveStars'] = $rating->getCount('2','5');
 $test['count']['all'] = $rating->getCount('2','all');
 $test['count']['accepted'] = $rating->getCount('2','accepted');
 $test['oldRating'] = $rating->getCount('2','oldRating');
 $test['newRating'] = $rating->getCount('2','newRating');
