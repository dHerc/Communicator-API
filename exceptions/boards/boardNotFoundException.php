<?php
namespace Communicator\Exceptions\Boards;

use Communicator\Exceptions\ItemNotFoundException as ItemNotFoundException;

class BoardNotFoundException extends ItemNotFoundException 
{
    public function __construct(int $id) {
        parent::__construct("board with id $id does not exist",2);
    }
}
