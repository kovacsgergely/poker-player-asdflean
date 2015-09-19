<?php

class Player
{
    const VERSION = "AsdfLEAN 1.5";

    public function betRequest($game_state)
    {
    	$holeCards = array();
    	$stack = 1000;
    	$myPlayer = $this->getMyPlayer($game_state);
    	if ($myPlayer) {
    		$holeCards = $myPlayer['hole_cards'];
    		$stack = $myPlayer['stack'];
    	}
    	if (count($holeCards) > 1) {
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
    	}
    	/*foreach ($holeCards as $card) {
    		if ($this->isFigure($card)) {
    			return $stack;
    		}
    	}*/
    	if ($this->hasPocketPair($game_state)) {
    		return $stack;
    	}
    	
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
}
