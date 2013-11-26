<?php
/**
 * Ordre d'aspiration
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Siwayll\Mollicute;

use Siwayll\Mollicute\Exception;

/**
 * Ordre d'aspiration
 */
class Command
{
    private $callBack = null;

    private $curlOpt = [];

    public $write = false;

    /**
     *
     * @param string $url url à aspirer
     */
    public function __construct($url = null)
    {
        $this->url = $url;
    }

    /**
     * Renvois l'url à aspirer
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Renvois les modifications de configuration à apporter à curl
     *
     * @return array
     */
    public function getCurlOpts()
    {
        return $this->curlOpt;
    }

    /**
     * Passe en mode écriture
     *
     * @return self
     */
    public function write()
    {
        $this->write = true;
        return $this;
    }

    /**
     * Ajoute une fonction qui sera chargée après l'aspiration
     *
     * @param callable $callback
     *
     * @return self
     * @throws Exception
     */
    public function setCallBack($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Callback invalide');
        }
        $this->callBack = $callback;

        return $this;
    }

    /**
     * Renvois la fonction de callback
     *
     * @return callable
     */
    public function getCallBack()
    {
        return $this->callBack;
    }

    /**
     * Indique si une fonction de callback est présente
     *
     * @return boolean
     */
    public function hasCallBack()
    {
        if (!empty($this->callBack)) {
            return true;
        }

        return false;
    }

}