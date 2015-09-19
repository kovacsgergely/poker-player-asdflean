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
    	switch ($flop) {
    		case 0 :
    			if ($handStrength >= 7) {
					return $stack;
				}
				break;
			case 3 :
				if ($handStrength >= 6) {
					return $stack;
				}
				break;
			case 4 :
				if ($handStrength >= 5) {
					return $stack;
				}
				break;
			case 5 :
				if ($handStrength >= 4) {
					return $stack;
				}
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
		$retval['cards'] = $game_state['community_cards'];
		$retval['turn'] = count($game_state['community_cards']);
		
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
}
