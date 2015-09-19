<?php

class Player
{
    const VERSION = "AsdfLEAN 1.8";

    public function betRequest($game_state)
    {
    	$holeCards = array();
    	$stack = 1000;
    	$myPlayer = $this->getMyPlayer($game_state);
    	if ($myPlayer) {
    		$holeCards = $myPlayer['hole_cards'];
    		$stack = $myPlayer['stack'];
    	}
    	
    	$handStrength = $this->getHandStrength(
    		$this->getHand($game_state)
    	);
    	
    	$communityCards = $this->getCommunityCards($game_state);
    	$flop = $communityCards['turn'];
    	$call = $game_state['current_buy_in'] - $myPlayer['bet'];
    	$raised = ($game_state['current_buy_in'] > ($game_state['small_blind'] * 2));
    	//$raised = $this->isRaised($game_state);
    	$raise = 0;
    	if ($raised) {
    		$raise = $game_state['current_buy_in'];
    	}
    	
    	$leanMasters = array();
    	$outCount = 0;
    	$foldCount = 0;
    	foreach ($game_state['players'] as $player) {
    		if (
    			$player['name'] == 'LeanMasters' &&
    			$player['status'] == 'active'
    		) {
    			$leanMasters = $player;
    		}
    		if ($player['status'] == 'out') {
    			$outCount++;
    		}
    		if ($player['status'] == 'folded') {
    			$foldCount++;
    		}
    	}
    	
    	if (
    		($outCount + $foldCount) == 3 &&
    		$foldCount > 0 &&
    		!empty($leanMasters)
    	) {
    		if ($leanMasters['bet'] > $game_state['small_blind'] * 2) {
    			if ($handStrength >= 4) {
    				return $stack;
    			} else {
    				return $call;
    			}
    		}
    	}
    	
    	switch ($flop) {
    		case 0 :
    			//preflop
    			if ($stack < $game_state['small_blind'] * 10) {
    				return $stack;
    			}
    			if (
    				$raised &&
    				$raise > $stack / 3
    			) {
    				if ($handStrength >= 9) {
    					return $stack;
    				} elseif ($handStrength >= 8) {
    					return $call;
    				}
    			} elseif ($raised) {
    				if ($handStrength >= 7) {
    					return $stack;
    				} elseif ($handStrength >= 5) {
    					return $call;
    				}
    			}
    			if (!$raised) {
    				if ($handStrength >= 8) {
    					return $stack;
    				} elseif ($handStrength >= 3) {
    					return $call + $game_state['small_blind'] * 8;
    				}
    			}
    			/*if ($handStrength >= 8) {
					return $stack;
				} elseif ($handStrength >= 7) {
					return $call;
				}*/
				break;
			case 3 :
				if ($this->has4Flush($game_state)) {
					return $stack;
				}
				
				$bestHand = $this->getBestHand($game_state);
				
				if (
					$bestHand['bestHand'] &&
					$bestHand['bestHand'] != $bestHand['bestFlop']
				) {
					if (
						$bestHand['bestHand'] != 'pair' ||
						$this->isStrongestPair($game_state)
					) {
						return $stack;
					}
				}
				break;
			case 4 :
			case 5 :
				$bestHand = $this->getBestHand($game_state);
				
				if (
					$bestHand['bestHand'] &&
					$bestHand['bestHand'] != $bestHand['bestFlop']
				) {
					if (
						$bestHand['bestHand'] != 'pair' ||
						$this->isStrongestPair($game_state)
					) {
						return $stack;
					}
				}
				if ($this->hasFlush($game_state)) {
					return $stack;
				}
				
				/*if (count($this->getCardRanks($game_state))) {
					if ($handStrength >= 7) {
						return $stack;
					}
				}
				if ($handStrength >= 8) {
					return $stack;
				}*/
				break;
    	}
    	
    	/*if (count($holeCards) > 1) {
    		if ($holeCards[0]['rank'] == 'A') {
    			return $stack;
    		}
    		if (
    			(
    				$this->isFigure($holeCards[0]) &&
    				(
    					$this->isFigure($holeCards[1]) ||
    					$holeCards[1]['rank'] > 6
    				)
    			) || (
    				$this->isFigure($holeCards[1]) &&
    				(
    					$this->isFigure($holeCards[0]) ||
    					$holeCards[0]['rank'] > 6
    				)
    			)
    		) {
    			return $stack;
    		}
    	}*/
    	
    	/*foreach ($holeCards as $card) {
    		if ($this->isFigure($card)) {
    			return $stack;
    		}
    	}*/
    	/*if ($this->hasPocketPair($game_state)) {
    		return $stack;
    	}*/
    	
        return 0;
    }

    public function showdown($game_state)
    {
    }
    
    public function getMyPlayer($game_state)
	{
		$retval = array();
		if (!isset($game_state['players'])) {
			return $retval;
		}
		foreach ($game_state['players'] as $player) {
    		if (isset($player['hole_cards']) && !empty($player['hole_cards'])) {
				$retval = $player;
				break;
			}
		}
			
		return $retval;
    }
    
    public function isFigure($card)
	{
		if (!in_array($card['rank'], array(2, 3, 4, 5, 6, 7, 8, 9, 10))) {
			return true;
		}
		
		return false;
	}
	
	public function getCommunityCards($game_state)
	{
		$retval = array(
			'cards' => '',
			'turn' => 0,
		);
		if (isset($game_state['community_cards'])) {
			$retval['cards'] = $game_state['community_cards'];
			$retval['turn'] = count($game_state['community_cards']);
		}
		
		return $retval;
	}
	
	public function hasPocketPair($game_state)
	{
		$player = $this->getMyPlayer($game_state);
		
		if (count($player['hole_cards']) < 2) {
			return false;
		}
		if ($player['hole_cards'][0]['rank'] == $player['hole_cards'][1]['rank'])
		{
			return true;
		}
		
		return false;	
	}
	
	public function getHand($game_state)
	{
		$player = $this->getMyPlayer($game_state);
		$cards = $player['hole_cards'];
		
		$ranks = array();
		if (count($cards) > 0) {
			$ranks[] = (string)$cards[0]['rank'];
			if (count($cards) > 1) {
				$ranks[] = (string)$cards[1]['rank'];
			}
		}
		sort($ranks);
		
		return strtoupper(implode('', $ranks));
	}
	
	public function getHandStrength($rankValue)
	{
		$retval = 0;
		
		$values = array(
			'1' => array(
				'3J',
				'2J',
				'310',
				'210',
				'59',
				'49',
				'34',
				'23',
			),
			'2' => array(
				'4Q',
				'3Q',
				'5J',
				'4J',
				'610',
				'510',
				'410',
				'56',
			),
			'3' => array(
				'3K',
				'2K',
				'6Q',
				'5Q',
				'7J',
				'6J',
				'810',
				'710',
				'67',
				'33',
				'22',
			),
			'4' => array(
				'2A',
				'5K',
				'4K',
				'8Q',
				'7Q',
				'9J',
				'8J',
				'910',
				'89',
				'78',
				'55',
				'44',
			),
			'5' => array(
				'5A',
				'4A',
				'3A',
				'7K',
				'6K',
				'9Q',
				'10J',
				'77',
				'66',
			),
			'6' => array(
				'6A',
				'5A',
				'9K',
				'8K',
				'10Q',
				'99',
				'88',
			),
			'7' => array(
				'8A',
				'7A',
				'10K',
				'JQ',
				'1010',
			),
			'8' => array(
				'JA',
				'10A',
				'9A',
				'KQ',
				'JK',
				'JJ',
			),
			'9' => array(
				'AA',
				'AK',
				'KK',
				'QQ',
				'AQ',
			),
		);
		
		foreach($values as $handRank => $hands) {
			foreach ($hands as $hand) {
				if ($rankValue == $hand) {
					$retval = $handRank;
				}
			}	
		}
		
		return (int)$retval;
	}
	
	/*public function getCardRanks($game_state) {
		$ranks = array();
		if (isset($game_state['community_cards'])) {
			$community_cards = $game_state['community_cards'];
			foreach ($community_cards as $value) {
				$ranks[] = $value['rank'];
			}
		}
		
		$player = $this->getMyPlayer($game_state);
		if ($player) {
			$cards = $player['hole_cards'];
			foreach ($cards as $value) {
				$ranks[] = $value['rank'];
			}
		}
		sort($ranks);
	
		$retranks = array();
		foreach($ranks as $value)
		{
			strtoupper($value);
			if(array_key_exists($value,$retranks))
			{
				$retranks[$value]++;
			}
			else
			{
				$retranks[$value] = 1; 
			}
		}
	
		foreach($retranks as $key => $value)
		{
			if($value == 1)
			{
				unset($retranks[$key]);
			}
		}
	
		return $retranks;
	}*/
	
	public function isRaised($game_state)
	{
		$myPos = $this->getMyPosition($game_state);
		$retval = false;
		switch ($myPos)
		{
			case -1: {
				break;
			}
			case -2: {
				if ($game_state['pot'] > $game_state['small_blind']) {
					$retval = true;
				}
				break;
			}
			default: {
				if ($game_state['pot'] > 3 * $game_state['small_blind']) {
					$retval = true;
				}
				break;
			}
		}
	
		return $retval;
	}
	
	public function getMyPosition($game_state)
	{
		$myPos = $game_state['dealer'] - $game_state['in_action'];
	
		 return $myPos;
	}
	
	public function getBestHand($game_state)
	{
		$retval = array(
			'bestHand' => '',
			'bestFlop' => '',
		);
		
		$communityCards = $this->getCommunityCards($game_state);
		if ($communityCards['turn'] < 3)
		{
			return $retval;
		}
		
		$player = $this->getMyPlayer($game_state);
		$commCards = $communityCards['cards'];
		$playCards = $player['hole_cards'];
		$allCards = array_merge($commCards, $playCards);
		
		$retval['bestHand'] = $this->isDrillPoker($this->getCardRanks($allCards));
		$retval['bestFlop'] = $this->isDrillPoker($this->getCardRanks($commCards));
		
		return $retval;
		
	}
	
	public function isStrongestPair($game_state)
	{
		$communityCards = $this->getCommunityCards($game_state);
		if ($communityCards['turn'] < 3)
		{
			return $retval;
		}
		
		$player = $this->getMyPlayer($game_state);
		$commCards = $communityCards['cards'];
		$playCards = $player['hole_cards'];
		
		$values = array();
		foreach ($commCards as $card) {
			$values[] = $this->getCardValue($card);
		}
		foreach ($playCards as $card) {
			$values[] = $this->getCardValue($card);
		}
		$maxValue = max($values);
		
		$maxValueOnTable = false;
		foreach ($commCards as $card) {
			if ($this->getCardValue($card) == $maxValue) {
				$maxValueOnTable = true;
				break;
			}
		}
		$maxValueInHand = false;
		foreach ($commCards as $card) {
			if ($this->getCardValue($card) == $maxValue) {
				$maxValueInHand = true;
				break;
			}
		}
		
		return ($maxValueOnTable && $maxValueInHand);
	}
	
	public function getCardValue($card)
	{
		switch ($card['rank']) {
		case 'J' : return 11;
		case 'Q' : return 12;
		case 'K' : return 13;
		case 'A' : return 14;
		}
		
		return $card['rank'];
	}
	
	public function getCardRanks($cards) {
		$ranks = array();
		
		foreach ($cards as $card) {
			$ranks[] = $card['rank'];
		}
	
		sort($ranks);
	
		$retranks = array();
		foreach($ranks as $value)
		{
			strtoupper($value);
			if(array_key_exists($value,$retranks))
			{
				$retranks[$value]++;
			}
			else
			{
				$retranks[$value] = 1; 
			}
		}
	
		foreach($retranks as $key => $value)
		{
			if($value == 1)
			{
				unset($retranks[$key]);
			}
		}
	
		return $retranks;
	}
	
	public function isDrillPoker($cardRanks) {
		$retval = '';
		$pair = 0;
		$drill = 0;
		$full = 0;
		$poker = 0;
		$doublepair = 0;
				
		foreach ($cardRanks as $key => $value) {
			if ($value == 2)
			{
				if ($pair == 1)
				{
					$doublepair = 1;
					$pair = 0;
				}
				$pair = 1;
			}
			else if ($value == 3)
			{
				if($pair)
				{
					$full = 1;
					$pair = 0;
				}
			}
			else if ($value == 4)
			{
				$poker = 1;
			}		
		}
		
		foreach (array('pair' => $pair, 'drill' => $drill, 'full' => $full, 'poker' => $poker, 'doublepair' => $doublepair) as $key => $value)
		{
			if ($value == 1)
			{
				$retval = $key;
			}
		}
		
		return $retval;		
	}
	
	public function hasFlush($game_state){
		$suits = array();
		if (isset($game_state['community_cards'])) {
			$community_cards = $game_state['community_cards'];
			foreach ($community_cards as $value) {
				$suits[] = $value['suit'];
			}
		}
	
		$player = $this->getMyPlayer($game_state);
		if ($player) {
			$cards = $player['hole_cards'];
			foreach ($cards as $value) {
				$suits[] = $value['suit'];
			}
		}
		$retsuits = array();
		foreach($suits as $value)
		{
			if(array_key_exists($value,$retsuits))
			{
				$retsuits[$value]++;
			}
			else
			{
				$retsuits[$value] = 1; 
			}
		}
		foreach ($retsuits as $key => $value) {
			if($value >= 5){
				return true;
			}
		}
		return false;
	}
	
	public function has4Flush($game_state){
		$suits = array();
		if (isset($game_state['community_cards'])) {
			$community_cards = $game_state['community_cards'];
			foreach ($community_cards as $value) {
				$suits[] = $value['suit'];
			}
		}
	
		$player = $this->getMyPlayer($game_state);
		if ($player) {
			$cards = $player['hole_cards'];
			foreach ($cards as $value) {
				$suits[] = $value['suit'];
			}
		}
		$retsuits = array();
		foreach($suits as $value)
		{
			if(array_key_exists($value,$retsuits))
			{
				$retsuits[$value]++;
			}
			else
			{
				$retsuits[$value] = 1; 
			}
		}
		foreach ($retsuits as $key => $value) {
			if($value == 4){
				return true;
			}
		}
		return false;
	}
	
	public function convertToNum($card) 
	{
		switch ($card['rank'])
		{
			case 'J':
			{
				$card['rank'] = 11;
				break;
			}
			case 'Q':
			{
				$card['rank'] = 12;
				break;
			}
			case 'K':
			{
				$card['rank'] = 13;
				break;
			}
			case 'A':
			{
				$card['rank'] = 14;
				break;
			}
			default:
			{
				break;
			}
			
			return $card;
		}
	}
	
	public function hasRow($game_state)
	{
		$allCards = array_merge($this->getMyPlayer($game_state)['hole_cards'], $game_state['community_cards']);
		$cardNum = count($allCards);
		
		if ($cardNum < 5) {
			return false;
		} 
		
		foreach ($allCards as $key => $value) {
			$allCards[$key] = $this->convertToNum($card);
		}
		
		for ($i = 0; $i < 5; $i++) {
			if ($allCards[0]['rank'] == 14) {
				$allCards[0]['rank'] = 1;
			}
			
			if($allCards[$i]['rank'] + 1 != $allCards[$i + 1]['rank']) {
				return false;
			}
		}
		
		if ($cardNum > 5) {
			for ($i = 1; $i < 6; $i++) {
				if ($allCards[1]['rank'] == 14) {
					$allCards[1]['rank'] = 1;
				}
				
				if($allCards[$i]['rank'] + 1 != $allCards[$i + 1]['rank']) {
					return false;
				}
			}
		}
		
		if ($cardNum > 6) {
			for ($i = 2; $i < 7; $i++) {
				if ($allCards[2]['rank'] == 14) {
					$allCards[2]['rank'] = 1;
				}
				
				if($allCards[$i]['rank'] + 1 != $allCards[$i + 1]['rank']) {
					return false;
				}
			}
		}
		
		return true;		
	}
}
