<?php
namespace Communicator\Exceptions\Boards;

use Communicator\Exceptions\ItemNotFoundException as ItemNotFoundException;

/**
 * Wyjątek oznaczający, że łańcuch nie został znaleziony
 */
class ChainNotFoundException extends ItemNotFoundException 
{
    public function __construct(int $id, int $chain_pos = null) {
        if($chain_pos)
            parent::__construct("chain from board with id $id on position $chain_pos does not exist",2);
        else
            parent::__construct("chain with id $id does not exist",2);
    }
}
