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
    private $curl = null;


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
     */
    public function setOpt(array $opts)
    {
        curl_setopt_array($this->curl, $opts);
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
            $line = '{.c:red}' . curl_error($this->curl) . '{.reset}';
        }
        Display::line($line);
        return $content;
    }
}
