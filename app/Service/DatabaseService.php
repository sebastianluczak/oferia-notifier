<?php

namespace App\Service;

use Medoo\Medoo;

/**
 * Class DatabaseService
 * @package App\Service
 */
class DatabaseService
{
    /**
     * @var Medoo
     */
    protected $database;

    /**
     * DatabaseService constructor.
     */
    public function __construct()
    {
        $this->database = new Medoo([
            'database_type' => getenv('DATABASE_TYPE'),
            'database_name' => getenv('DATABASE_NAME'),
            'server'        => getenv('DATABASE_SERVER'),
            'username'      => getenv('DATABASE_USER'),
            'password'      => getenv('DATABASE_PASS'),
        ]);
    }

    /**
     * @return Medoo
     */
    public function getDatabase(): Medoo
    {
        return $this->database;
    }

    /**
     * @param string $link
     * @param string $offerName
     * @return bool|\PDOStatement
     */
    public function insertOffer(string $link, string $offerName)
    {
        $verify = $this->database->insert(getenv('DATABASE_TABLE'), [
            'url' => $link,
            'title' => $offerName,
            'uuid' => $this->generateUUID($offerName, $link)
        ]);

        return $verify;
    }

    /**
     * @param string $link
     * @param string $offerName
     * @return bool
     */
    public function checkIfOfferExists(string $link, string $offerName)
    {
        $data = $this->database->select(getenv('DATABASE_TABLE'), [
            'id'
        ], [
            'uuid' => $this->generateUUID($offerName, $link)
        ]);

        if (count($data) == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $offerName
     * @param string $link
     * @return string
     */
    protected function generateUUID(string $offerName, string $link)
    {
        return md5($offerName . '----' . $link);
    }
}
