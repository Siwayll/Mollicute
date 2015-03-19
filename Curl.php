<?php
/**
 * Aspiration via Curl
 *
 * @package    Libs
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Siwayll\Mollicute;

use Siwayll\Deuton\Display;
use Monolog\Logger;

/**
 * Aspiration via Curl
 *
 * @package    Libs
 * @subpackage Core
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
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
     *
     * @param \Monolog\Logger $logger
     *
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->log = $logger;

        return $this;
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
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $line = '{.c:blue}  curl{.reset}  ' . $url . ' ';
        Display::write($line);
        $content = curl_exec($this->curl);
        if (curl_error($this->curl) === '') {
            $line = '{.c:green}ok{.reset}';
        } else {
            $this->log->addWarning('Erreur aspiration : ' . curl_error($this->curl));
            $line = '{.c:red}' . curl_error($this->curl) . '{.reset}';
        }
        Display::line($line);
        return $content;
    }
}
