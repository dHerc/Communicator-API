<?php
namespace Communicator\Exceptions\Boards;

use Communicator\Exceptions\ItemNotFoundException as ItemNotFoundException;

class ChainNotFoundException extends ItemNotFoundException 
{
    public function __construct(int $board_id, int $chain_pos) {
        parent::__construct("chain from board with id $board_id on position $chain_pos does not exist",2);
    }
}
