<?php declare(strict_types=1);
namespace Communicator\Model\Boards\Notes\Helpers;

use Communicator\Exceptions\Boards\InvalidFormatException;

/**
 * Klasa służąca do przechowywania informacji o notatce zawierającej kontakt
 */
class Contact extends Content
{
    /**
     * Informacje odnośnie kontaktu
     * @var object
     */
    public object $contact;

    public function __construct(string|array $data)
    {
        if(gettype($data) === 'string')
            $contact = json_decode($data);
        else
            $contact = json_decode(json_encode($data));
        if(!$contact)
            throw new InvalidFormatException('contact');
        if(isset($contact->phone)) {
            $contact->phone = $this->sanitizePhone(strval($contact->phone));
            if (!$this->validatePhone($contact->phone))
                throw new InvalidFormatException('contact phone');
        }
        if(isset($contact->email) && !$this->validateEmail($contact->email))
            throw new InvalidFormatException('contact email');
        $this->contact = $contact;
    }

    /**
     * Funkcja usuwająca z numeru telefonu niepotrzebne znaki
     * @param string $phone Numer telefonu
     * @return string
     */
    private function sanitizePhone(string $phone): string
    {
        return preg_replace("/[\s-]/", "", $phone);
    }

    /**
     * Funkcja sprawdzająca poprawność podanego numeru telefonu
     * @param string $phone Numer telefonu do sprawdzenia
     * @return bool Informacja czy numer ma poprawny format
     */
    private function validatePhone(string $phone): bool
    {
        return boolval(preg_match("/\+?\d{4,15}/",$phone));
    }

    /**
     * Funkcja sprawdzająca poprawność podanego adresu email
     * @param string $email Numer telefonu do sprawdzenia
     * @return bool Informacja czy email ma poprawny format
     */
    private function validateEmail(string $email): bool
    {
        return boolval(filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public function get(): string
    {
        return json_encode($this->contact);
    }
}