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
     * @return self
     */
    public function setOpt(array $opts)
    {
        curl_setopt_array($this->curl, $opts);

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
            $this->log->addWarning('Erreur aspiration');
            $line = '{.c:red}' . curl_error($this->curl) . '{.reset}';
        }
        Display::line($line);
        return $content;
    }
}
