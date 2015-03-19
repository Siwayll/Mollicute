<?php

namespace Siwayll\Mollicute\Curl;

/**
 * Gestionnaire header
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Header
{
    private static $persistentHeader = [];

    private $header = [];

    /**
     * Remise à zéro des paramétrages
     *
     * @return void
     */
    public static function reset()
    {
        self::$persistentHeader = [];
    }

    /**
     * Chargement du header
     */
    public function __construct()
    {
        $this->setFormatedArray(self::$persistentHeader);
    }

    /**
     * Enregistre un tableau déja formaté pour curl
     *
     * Referer est ignoré.
     *
     * @param string[] $curlHeaders Tableau déjà formaté
     *
     * @return self
     */
    public function setFormatedArray(array $curlHeaders)
    {
        foreach ($curlHeaders as $headOpt) {
            $optArr = explode(':', $headOpt);
            if ($optArr[0] == 'Referer') {
                continue;
            }
            $this->set(trim($optArr[0]), trim($optArr[1]));
        }
        return $this;
    }

    /**
     * Enregistre un attribut
     *
     * @param string $name  Nom de l'attribut du header
     * @param string $value Valeur
     *
     * @return self
     */
    public function set($name, $value)
    {
        $this->header[$name] = $value;
        return $this;
    }

    /**
     * Supprime une option du header
     *
     * @param string $name Nom de l'option
     *
     * @return self
     */
    public function rm($name)
    {
        if (isset($this->header[$name])) {
            unset($this->header[$name]);
        }
        return $this;
    }

    /**
     * Enregistre un tableau associatif
     *
     * @param array $headers Tableau sous la forme clé / valeur
     *
     * @return self
     */
    public function setArray(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Format le header pour qu'il soit acceptable pour curl
     *
     * @return string[]
     */
    private function formatHeader()
    {
        $head = [];
        foreach ($this->header as $key => $value) {
            $head[] = $key . ': ' . $value;
        }
        self::$persistentHeader = $head;

        return $head;
    }

    /**
     * Renvois le header
     *
     * @return string[]
     */
    public function getHeader()
    {
        if (!empty($this->header)) {
            return $this->formatHeader();
        }

        return self::$persistentHeader;
    }
}
