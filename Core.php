<?php
/**
 * Mollicute
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Siwayll\Mollicute;

use Siwayll\Deuton\Display;
use Monolog\Logger;

/**
 * Mollicute
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Core
{
    /**
     * Liste des Command à exécuter
     *
     * @var \Siwayll\Mollicute\Command[]
     */
    private $plan = [];

    /**
     * Commande courante
     *
     * @var \Siwayll\Mollicute\Command
     */
    private $curCmd;

    /**
     * Résultat de l'aspiration de la commande
     *
     * @var string
     */
    private $curContent;

    /**
     * Systeme de log monolog
     *
     * @var Logger
     */
    private $log;

    /**
     * Création d'un plan d'aspiration Mollicute
     */
    public function __construct()
    {
    }

    /**
     * Paramétrage du log
     *
     * @param Logger $logger Système de log
     *
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->log = $logger;
        return $this;
    }

    /**
     * Ajout d'un plugin
     *
     * @param string $name Namespace du plugin
     *
     * @return self
     */
    public function addPlugin($name)
    {
        Command::addPugin($name);

        $coreName = $name . '\Core';
        $this->plugins[] = new $coreName;
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
        foreach ($this->plugins as $plugin) {
            if (is_callable([$plugin, $name])) {
                $return = call_user_func_array([$plugin, $name], $args);
                if (
                    $return !== null
                    && (
                        is_object($return)
                        && get_class($return) !== get_class($plugin)
                    )
                ) {
                    return $return;
                }
                return $this;
            }
        }

        throw new Exception('Aucune fonction à ce nom');
    }

    /**
     * Initialisation du plan
     *
     * @param \Siwayll\Mollicute\Command $start Première aspiration
     *
     * @return self
     */
    public function startWith(Command $start)
    {
        $this->plan = [$start];
        return $this;
    }

    /**
     * Lance l'éxécution de l'aspiration
     *
     * @return void
     */
    public function run()
    {
        $curl = new Curl();

        if (empty($this->log)) {
            throw new Exception('Un logger est nécessaire');
        }
        $curl->setLogger($this->log);

        do {
            $this->curContent = null;
            $this->curCmd = array_pop($this->plan);
            $this->log->addDebug('hit', [$this->curCmd]);
            $line = '{.c:blue} count{.reset}  ' . count($this->plan) . ' ';
            Display::line($line);
            try {
                foreach ($this->plugins as $plugin) {
                    if (method_exists($plugin, 'before')) {
                        $plugin->before($this->curCmd, $this);
                    }
                }
            } catch (\Siwayll\Mollicute\Abort $exc) {
                $this->log->addNotice(
                    'Annulation ordre',
                    [$this->curCmd, $exc]
                );
                continue;
            }
            $this->exec('CallPre');

            // Paramétrage curl
            $curl->setOpts($this->curCmd->getCurlOpts());

            // éxecution curl
            $this->curContent = $curl->exec($this->curCmd->getUrl());

            $this->exec('CallBack');

            foreach ($this->plugins as $plugin) {
                if (method_exists($plugin, 'after')) {
                    $plugin->after($this->curCmd, $this->curContent, $this);
                }
            }

            $this->exec('CallAfterPlug');

            if ($this->curCmd->getSleep() !== false) {
                sleep($this->curCmd->getSleep());
            }
        } while (!empty($this->plan));
    }

    /**
     * Exécution d'une callback
     *
     * @param string $stepName Nom de l'étape
     *
     * @return self
     */
    private function exec($stepName = 'CallBack')
    {
        $funcTestName = 'has' . $stepName;
        if ($this->curCmd->$funcTestName() !== true) {
            return $this;
        }
        unset($funcTestName);

        $funcName = 'get' . $stepName;
        foreach (call_user_func($this->curCmd->$funcName(), $this->curContent, $this->curCmd) as $cmd) {
            if (is_a($cmd, '\\Siwayll\\Mollicute\\Command')) {
                $this->add($cmd);
            }
        }

        return $this;
    }

    /**
     * Ajoute une liste de commandes au plan d'aspiration
     *
     * @param \Siwayll\Mollicute\Command[] $cmds Liste de Command
     *
     * @return self
     */
    public function addToPlan(array $cmds)
    {
        $this->plan = array_merge($this->plan, $cmds);

        return $this;
    }

    /**
     * Ajoute une commande au plan d'aspiration
     *
     * @param \Siwayll\Mollicute\Command $cmd Ordre d'aspiration à ajouter
     *
     * @return self
     */
    public function add(Command $cmd)
    {
        $this->plan[] = $cmd;

        return $this;
    }
}
