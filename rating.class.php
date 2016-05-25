<?php
/* This code protected by GPL v2
 * Author: Astapenko Sergey a.k.a. McLotos
 * Date: 09.july.2015
 */

 class Rating {

 public $table=array(
                      'elem'=>'elem', //id of element in ratings table
                      'eId'=>'id',//id of element in elements table
                      'rating'=>'rating', //established rating in ratings table
                      'date'=>'date',//date of established rating in ratings table
                      'eName'=>'name' //name of element in elements table
                    );
 
 private $elems; //all elements
 private $allRatings; //all votes from ratings table
 private $maxLive = 90; //time to live vote before weight begin to decline
 private $minVotesCount = 10; //min count of votes, to get into the rating
 private $countAllVotes; //count of all votes
 private $countNotNullVotes; //count of votes more than 0
 private $currentRatingValue; //rating of current element
 private $amendment = 10; //statistic amendment

 public function __construct($table, $elemList, $allRatings, $voteMaxLive, $minVotesCount) {
    $this->table = $table;
    $this->allRatings = $allRatings;
    $this->maxLive = $voteMaxLive;
    $this->minVotesCount = $minVotesCount;
    $this->elems = $elemList;
    $this->createArrays();
 }
 
public function getCount($elemId, $rating) {
    for($i=0; $i<count($this->elems); $i++) {
        if($this->elems[$i]['elemId']==$elemId){
            $this->getOldRating($i);
            switch($rating){
                case '0':return $this->elems[$i]['zeroStarsCount'];break;//count of 0 votes
                case '1':return $this->elems[$i]['oneStarsCount'];break;//count of 1 votes
                case '2':return $this->elems[$i]['twoStarsCount'];break; //count of 2
                case '3':return $this->elems[$i]['threeStarsCount'];break;//count of 3
                case '4':return $this->elems[$i]['fourStarsCount'];break;//count of 4
                case '5':return $this->elems[$i]['fiveStarsCount'];break;//count of 5
                case 'all':return $this->elems[$i]['countVotes'];break; //count of all votes
                case 'accepted':return $this->elems[$i]['acceptedVotes'];break; //count of accepted votes
                case 'oldRating':return round($this->elems[$i]['oldRating'],2);break; //rating as sum/count
                case 'newRating':return round($this->elems[$i]['newRating'],2);break; //Bayce result
            }    
        }
    }
 }

 private function createArrays() {
     for($e=0; $e<count($this->elems); $e++) {
         $this->changeStructure($e);
      }
      $this->countAllVotes = count($this->allRatings);
      for($r=0; $r<$this->countAllVotes; $r++) {
          for($e=0; $e<count($this->elems); $e++) {
              if($this->elems[$e]['elemId']==$this->allRatings[$r][$this->table['elem']]) {
                  $this->setCountVotes($e,$r);
                  $this->addVote($e, $r);
              }
          }
          if($this->allRatings[$r][$this->table['rating']]>0) {
              $this->countNotNullVotes+=1;
          }
      }
      $this->getAcceptedRating();
 }
 
 private function getOldRating($elem) {
     $this->elems[$elem]['oldRating'] += $this->elems[$elem]['oneStarsCount'];
     $this->elems[$elem]['oldRating'] += $this->elems[$elem]['twoStarsCount']*2;
     $this->elems[$elem]['oldRating'] += $this->elems[$elem]['threeStarsCount']*3;
     $this->elems[$elem]['oldRating'] += $this->elems[$elem]['fourStarsCount']*4;
     $this->elems[$elem]['oldRating'] += $this->elems[$elem]['fiveStarsCount']*5;
     $this->elems[$elem]['oldRating'] =  $this->elems[$elem]['oldRating']/$this->elems[$elem]['acceptedVotes'];
 }

 private function setCountVotes($pos,$rating) {
     $this->elems[$pos]['countVotes']+=1;
     if($this->allRatings[$rating][$this->table['rating']]>0) {
         $this->elems[$pos]['acceptedVotes']+=1;
     }
     if($this->elems[$pos]['elemId']==$this->allRatings[$rating][$this->table['elem']]) {
         switch($this->allRatings[$rating][$this->table['rating']]) {
             case '0': $this->elems[$pos]['zeroStarsCount']+=1;break;
             case '1': $this->elems[$pos]['oneStarsCount']+=1;break;
             case '2': $this->elems[$pos]['twoStarsCount']+=1;break;
             case '3': $this->elems[$pos]['threeStarsCount']+=1;break;
             case '4': $this->elems[$pos]['fourStarsCount']+=1;break;
             case '5': $this->elems[$pos]['fiveStarsCount']+=1;break;
         }
     }
 }

 private function getAcceptedRating() {
     $dividend2 = 0;
     $divider2 = 0;
     for($i=0; $i<count($this->elems); $i++) {
         $dividend1=0;
         $divider1=0;
         if($this->elems[$i]['acceptedVotes']>=$this->minVotesCount) {
             for($e=0; $e<count($this->elems[$i]['votes']); $e++) {
                 $dividend1+=$this->elems[$i]['votes'][$e]['rating']*$this->elems[$i]['votes'][$e]['voiceWeight'];
                 $divider1+=$this->elems[$i]['votes'][$e]['voiceWeight'];
                 $dividend2+=$dividend1;
                 $divider2+=$divider1;
              };
              $this->elems[$i]['average'] = $dividend1/$divider1;/*(R)*/
              $this->elems[$i]['acceptedRating'] = $divider1;/*(v)*/
          }
      }
      $this->currentRatingValue = $dividend2/$divider2; /*(C)*/
      for($i=0; $i<count($this->elems); $i++) {
          if($this->elems[$i]['acceptedVotes']>=$this->minVotesCount) {
              $this->elems[$i]['newRating']=$this->getResults($this->elems[$i]['average'], $this->elems[$i]['acceptedRating']);
          }
      }
 }

 private function getResults($R,$v) {
     return ((($R*$v+$this->currentRatingValue*$this->amendment)/($v+$this->amendment))-1)*25;     
 }
 
 private function addVote($pos, $rating) {
     $arr = array(
         'rating'=>$this->allRatings[$rating][$this->table['rating']],
         'howOld'=>$this->getHowOld($this->allRatings[$rating][$this->table['date']]),
         'voiceWeight'=>$this->ratesOfVotes($this->getHowOld($rating[$this->table['date']])) /*(k)*/
         );
     array_push($this->elems[$pos]['votes'], $arr);
 }

 private function ratesOfVotes($howOld) {
     return ($howOld<=$this->maxLive) ? 1 : 1.106*pow(M_E , -0.001697*$howOld);
 }

 private function getHowOld($date) {
     $dateOfComment = explode(' ', $date);
     $datetime1 = new DateTime($dateOfComment[0]);
     $datetime2 = new DateTime(date('Y-m-d'));
     $interval = $datetime1->diff($datetime2);
     return trim($interval->format('%R%a'),'+');
 }
 
 private function changeStructure($pos) {
     $this->elems[$pos] = array(
        'elemId'=>$this->elems[$pos][$this->table['eId']],
        'elemName'=>$this->elems[$pos][$this->table['eName']],
        'countVotes' => '0',
        'acceptedVotes'=> '0',
        'zeroStarsCount' => '0',
        'oneStarsCount' => '0',
        'twoStarsCount' => '0',
        'threeStarsCount' => '0',
        'fourStarsCount' => '0',
        'fiveStarsCount' => '0',
        'oldRating' => '0',
        'average'=> '0',
        'acceptedRating'=> '0',
        'newRating' => '0',
        'votes'=>array());
 }
}
