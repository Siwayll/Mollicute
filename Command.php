<?php
/**
 * Ordre d'aspiration
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Siwayll\Mollicute;

/**
 * Ordre d'aspiration
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Command
{
    /**
     * Fonction appellée après l'aspiration
     *
     * @var callable
     */
    private $callBack = null;

    /**
     * Fonction appellée avant l'aspiration
     *
     * @var callable
     */
    private $callPre = null;

    /**
     * Temps de pause après une aspiration
     *
     * @var boolean|int
     */
    protected $sleep = false;

    /**
     * Configuration curl
     *
     * @var [] Un tableau spécifiant quelles options à fixer avec leurs valeurs.
     * Les clés devraient être des constantes valides de curl_setopt()
     * ou leur entier équivalent.
     */
    private $curlOpt = [];

    private static $plugins = [];

    /**
     * Ajoute un plugin
     *
     * @param string $name nom de la classe du plugin
     *
     * @return void
     */
    final public static function addPugin($name)
    {
        $className = $name . '\\CommandPlug';
        self::$plugins[] = $className;
    }

    /**
     * Création d'un ordre d'aspiration
     *
     * @param string $url url à aspirer
     */
    public function __construct($url = null)
    {
        $this->url = $url;

        foreach (self::$plugins as $plugin) {
            foreach ($plugin::getVars() as $varName => $defaultValue) {
                $this->$varName = $defaultValue;
            }
        }
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
     * Modification de l'url
     *
     * @param string $newUrl Nouvelle url
     *
     * @return self
     */
    public function setUrl($newUrl)
    {
        $this->url = $newUrl;
        return $this;
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
     * @param string $code
     * @param string $value
     */
    public function setCurlOpt($code, $value)
    {
        $this->curlOpt[$code] = $value;

        return $this;
    }

    /**
     * Appel des fonctions des plugins
     *
     * @param string $name Nom de la fonction
     * @param mixed  $args Paramètres
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, array $args)
    {
        $params = [$this];
        foreach ($args as $opt) {
            $params[] = $opt;
        }
        foreach (self::$plugins as $plugin) {
            if (is_callable([$plugin, $name])) {

                return call_user_func_array([$plugin, $name], $params);
            }
        }

        throw new Exception('Aucune fonction à ce nom : ' . $name);
    }

    /**
     * Indique si une fonction de callback est présente
     *
     * @return boolean Vrais si elle est présente
     */
    public function hasCallBack()
    {
        if (!empty($this->callBack)) {
            return true;
        }

        return false;
    }

    /**
     * Ajoute une fonction qui sera chargée après l'aspiration
     *
     * @param callable $callback fonction de rappel
     *
     * @return Command
     * @throws Exception si le callback est invalide
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
     * @return boolean Vrais si elle est présente
     */
    public function hasCallPre()
    {
        if (!empty($this->callPre)) {
            return true;
        }

        return false;
    }

    /**
     * Ajoute une fonction qui sera chargée avant l'aspiration
     *
     * @param callable $callback fonction de rappel
     *
     * @return self
     * @throws Exception si le callback est invalide
     */
    public function setCallPre($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Callback invalide');
        }
        $this->callPre = $callback;

        return $this;
    }

    /**
     * Renvois la fonction de callback
     *
     * @return callable
     */
    public function getCallPre()
    {
        return $this->callPre;
    }

/**
     * Indique si une fonction de callback est présente
     *
     * @return boolean Vrais si elle est présente
     */
    public function hasCallAfterPlug()
    {
        if (!empty($this->callAfterPlug)) {
            return true;
        }

        return false;
    }

    /**
     * Ajoute une fonction qui sera chargée après le chargement des plugins
     * après l'aspiration
     *
     * @param callable $callback fonction de rappel
     *
     * @return Command
     * @throws Exception si le callback est invalide
     */
    public function setCallAfterPlug($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Callback invalide');
        }
        $this->callAfterPlug = $callback;

        return $this;
    }

    /**
     * Renvois la fonction de callback
     *
     * @return callable
     */
    public function getCallAfterPlug()
    {
        return $this->callAfterPlug;
    }

    /**
     * Parametrage du temps de pause après l'aspiration
     *
     * @param int $time temps en seconde
     *
     * @return self
     */
    public function setSleep($time)
    {
        $this->sleep = $time;

        return $this;
    }

    /**
     * Renvois le temps de pause à appliquer après l'aspiration
     *
     * @return boolean|int
     */
    public function getSleep()
    {
        return $this->sleep;
    }
}
