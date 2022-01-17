<?php declare(strict_types=1);

namespace Communicator\Model\Boards\Notes\Helpers;

/**
 * Klasa abstrakcyjna służąca do przechowywania dodatkowej zawartości notatki
 */
abstract class Content
{
    /**
     * Funkcja zwracająca zawartość w formie łańcucha tekstowego
     * @return string Zawartość w formie tekstu
     */
    abstract public function get(): string;
}