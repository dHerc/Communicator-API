<?php
namespace Communicator\Exceptions\Boards;

use Communicator\Exceptions\ItemNotFoundException as ItemNotFoundException;

class NoteNotFoundException extends ItemNotFoundException {

    public function __construct(int $id) {
        parent::__construct("note with id $id does not exist",2);
    }
}
