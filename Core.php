<?php
/**
 * Mollicute
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Siwayll\Mollicute;

use Monolog\Logger;
use Siwayll\Mollicute\Abort;

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
     *
     * @var Curl
     */
    protected $curl;

    /**
     * Chemin vers le fichier de sauvegarde du plan
     *
     * @var string
     */
    private $savePath;

    /**
     * Création d'un plan d'aspiration Mollicute
     */
    public function __construct()
    {
        $this->curl = new Curl();
    }

    /**
     * Retourne l'objet Curl utilisé
     *
     * @return Curl
     */
    public function getCurl()
    {
        return $this->curl;
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
     * Récupération d'un plan sauvegardé
     *
     * @return $this
     * @throws \Exception
     */
    public function recovery()
    {
        if (empty($this->savePath)) {
            throw new \Exception('Aucune reprise possible');
        }

        $data = file_get_contents($this->savePath);
        $this->plan = unserialize($data);

        return $this;
    }

    /**
     * Enregistre le chemin du fichier de sauvegarde
     *
     * @param string $path Chemin vers le fichier de sauvegarde
     *
     * @return $this
     */
    public function setSavePath($path)
    {
        $this->savePath = $path;
        return $this;
    }

    /**
     * Arrêt de l'aspiration
     *
     * @return self
     */
    public function stop()
    {
        if (!empty($this->savePath)) {
            $data = serialize($this->plan);
            file_put_contents($this->savePath, $data);
            unset($data);
        }

        return $this;
    }

    /**
     * Lance l'éxécution de l'aspiration
     *
     * @return void
     * @throws
     */
    public function run()
    {
        if (empty($this->log)) {
            throw new Exception('Un logger est nécessaire');
        }
        $this->curl->setLogger($this->log);

        $this->execPlugin('init');

        do {
            $this->curContent = null;
            $this->curCmd = array_pop($this->plan);

            try {
                $this->execPlugin('before', $this->curCmd);
                $this->exec('CallPre', $this->curCmd);
            } catch (Abort $exc) {
                continue;
            }

            // Paramétrage curl
            $this->curl->setOpts($this->curCmd->getCurlOpts());

            // éxecution curl
            $this->curContent = $this->curl->exec($this->curCmd->getUrl());

            $this->exec('CallBack', $this->curCmd);
            $this->execPlugin('after', $this->curCmd);
            $this->exec('CallAfterPlug', $this->curCmd);

            $this->sleep($this->curCmd);
        } while (!empty($this->plan));
    }

    /**
     * Temporisation
     *
     * @param Command $command Command courante
     */
    private function sleep(Command $command)
    {
        if ($command->getSleep() === false) {
            return;
        }
        $progress = null;
        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'getTickSleep')) {
                $progress = $plugin->getTickSleep();
                break;
            }
        }
        for ($i = $command->getSleep(); $i > 0; $i--) {
            if (is_callable($progress)) {
                $progress($i);
            }
            sleep(1);
        }
    }

    /**
     * Exécution d'une callback
     *
     * @param string  $stepName Nom de l'étape
     * @param Command $command  Command courante
     *
     * @return self
     * @throws Exception Des plugins
     * @throws Stop      Lors d'une demande d'arret de l'aspiration
     */
    private function exec($stepName = 'CallBack', Command $command)
    {
        $funcTestName = 'has' . $stepName;
        if ($command->$funcTestName() !== true) {
            return $this;
        }
        unset($funcTestName);

        $funcName = 'get' . $stepName;
        try {
            foreach (call_user_func($command->$funcName(), $this->curContent, $command) as $cmd) {
                if (is_a($cmd, '\\Siwayll\\Mollicute\\Command')) {
                    $this->add($cmd);
                }
            }
        } catch (Stop $exc) {
            $this->stop();
            throw $exc;
        }

        return $this;
    }

    /**
     * Execution d'un plugin
     *
     * @param string  $name    Nom de l'étape
     * @param Command $command Command courante
     *
     * @return self
     * @throws Exception Des plugins
     * @throws Stop      Lors d'une demande d'arret de l'aspiration
     * @throws Abort     Pour une annulation de l'ordre d'aspi courant
     */
    private function execPlugin($name, Command $command = null)
    {
        try {
            foreach ($this->plugins as $plugin) {
                if (method_exists($plugin, $name)) {
                    $plugin->$name($command, $this->curContent, $this);
                }
            }
        } catch (Abort $exc) {
            $this->log->addNotice(
                'Annulation ordre',
                [$command, $exc]
            );
            throw $exc;
        } catch (Stop $exc) {
            $this->stop();
            throw $exc;
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

    /**
     * Retourne le nombre d'éléments présent dans le plan d'aspiration
     *
     * @return int
     */
    public function countPlan()
    {
        return count($this->plan);
    }
}
