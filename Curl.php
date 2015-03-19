<?php

namespace Siwayll\Mollicute;

use Siwayll\Mollicute\Curl\Header;
use Monolog\Logger;

/**
 * Aspiration via Curl
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Curl
{
    /**
     * Curl
     *
     * @var ressource
     */
    private $curl = null;

    /**
     * Récupération des cookies
     *
     * @var boolean
     */
    private $cookieCatch = false;

    /**
     * Header Curl
     *
     * @var Header
     */
    public $header;

    /**
     *
     * @var Logger
     */
    private $log;

    /**
     * Initialisation d'une nouvelle connexion Curl
     */
    public function __construct()
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Paramétrage de curl
     *
     * @param array $opts Parametrages sous la forme __Option Curl__ => __value__
     *
     * @return void
     * @uses \Curl::setOpt application de la configuration
     */
    public function setOpts(array $opts)
    {
        foreach ($opts as $key => $value) {
            if (!is_int($key)) {
                $key = constant($key);
            }
            $this->setOpt($key, $value);
        }

        return $this;
    }

    /**
     * Paramètre l'aspiration curl
     *
     * Chaque option est enregistrée dans le tableau $options
     *
     * @param int    $key   L'option CURLOPT_XXX à définir.
     * @param string $value Valeur de l'option
     *
     * @return \Curl
     */
    public function setOpt($key, $value)
    {
        $this->options[$key] = $value;
        curl_setopt($this->curl, $key, $value);
        return $this;
    }

    /**
     * Paramètre l'aspiration curl qui ne peut être surchargé.
     *
     * @param int   $option L'option CURLOPT_XXX à définir.
     * @param mixed $value  La valeur à définir pour option.
     *
     * @return \Curl
     * @throws BotException lors de la redéfinition d'une opt finale.
     */
    public function setFinalOpt($option, $value)
    {
        if (isset($this->finalOpt[$option])) {
            $message = sprintf(CODE_ERROR_CURLMODIF, $option);
            throw new BotException(CODE_ERROR_CURLMODIF, $this->infoHit);
        }
        $this->finalOpt[$option] = $value;

        return $this;
    }

    /**
     * Paramettrage du logger
     *
     * @param \Monolog\Logger $logger Logger
     *
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->log = $logger;

        return $this;
    }

    /**
     * Active la récupération des cookies
     * ATTENTION cela nécéssite d'afficher le header dans le fichier
     *
     * @return self
     */
    public function catchCookies()
    {
        $this->cookieCatch = true;
        $this->setOpt(CURLOPT_HEADER, true);

        return $this;
    }

    /**
     * Annule la récupération des cookies
     *
     * @return self
     */
    public function catchCookiesStop()
    {
        $this->cookieCatch = false;
        $this->setOpt(CURLOPT_HEADER, false);

        return $this;
    }

    /**
     * Récupère les cookies dans le header
     *
     * @param string $content Resultat de l'aspiration
     *
     * @return \Curl
     */
    public function parseCookie($content)
    {
        if (!preg_match_all('#^Set-Cookie:\s*([^;]+)#mi', $content, $matchs)) {
            if (strpos($content, 'Set-Cookie') !== false) {
                throw new Exception('Aucun cookie');
            }
            $this->cookies = [];
            return;
        }

        $trashCookie = implode('&', $matchs[1]);
        parse_str($trashCookie, $this->cookies);

        return $this;
    }

    /**
     * Renvois le tableau des cookies
     *
     * @return []
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Renvois la ressource Curl
     *
     * @return type
     */
    public function get()
    {
        return $this->curl;
    }

    /**
     * Récupération du contenu de la page.
     *
     * @param string $url Url de la page à aspirer
     *
     * @return string
     */
    public function exec($url)
    {
        // Application des headers
        $headers = $this->header->getHeader();
        if (!empty($headers)) {
            $this->setOpt(CURLOPT_HTTPHEADER, $headers);
        }

        // chargement des options non modifiables
        foreach ($this->finalOpt as $option => $value) {
            $this->setOpt($option, $value);
        }
        $this->setOpt(CURLOPT_URL, $url);

        $this->log->addDebug('curl', [$url]);
        $content = curl_exec($this->curl);

        if (curl_error($this->curl) !== '') {
            $this->log->addWarning('Erreur aspiration : ' . curl_error($this->curl));
        }

        $this->infoHit = curl_getinfo($this->curl);

        return $content;
    }
}
