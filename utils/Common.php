<?php
namespace Communicator\Utils;

/**
 * Klasa zawierająca wspólne funkcje
 */
abstract class Common {

    /**
     * Funkcja zwracająca typ zmiennej lub klasę, jeżeli zmienna jest obiektem
     * @param mixed $content Zmienna
     * @return string Typ lub klasa zmiennej
     */
    public static function getTypeOrClass(mixed $content): string
    {
        $type = gettype($content);
        if($type == 'object')
            return get_class($content);
        else
            return $type;
    }

    /**
     * Funkcja zwracająca losowy ciąg znaków o podanej długości
     * @param int $length Długość
     * @return string Losowy ciąg znaków
     * @throws \Exception Wyjątek ten jest rzucany, jeżeli nie można znaleźć źródła losowości
     */
    public static function randomString(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = "";
        $maxIndex = strlen($characters) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $string .= $characters[random_int(0, $maxIndex)];
        }
        return $string;
    }
}
