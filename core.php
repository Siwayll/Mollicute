<?php
/**
 * Mollicute
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Siwayll\Mollicute;

use Siwayll\Mollicute\Exception;

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
     * Création d'un plan d'aspiration Mollicute
     *
     * @param \Siwayll\Mollicute\Command $start première instruction
     *
     * @return void
     */
    public function __construct(Command $start)
    {
        $this->plan[] = $start;
    }

    /**
     * Lance l'éxécution de l'aspiration
     *
     * @return void
     */
    public function run()
    {
        $curl = new \Libs\Curl();

        do {
            $this->curCmd = array_pop($this->plan);
            // Paramétrage curl
            $curl->setOpt($this->curCmd->getCurlOpts());
            // éxecution curl
            $content = $curl->exec($this->curCmd->getUrl());
            echo count($this->plan);

            $return = null;
            if ($this->curCmd->hasCallback()) {
                $return = call_user_func(
                    $this->curCmd->getCallback(),
                    $content
                );
            }

            // @todo mettre en place une gestion des fichiers
            if ($this->curCmd->write === true) {
                $fileName = '/var/www/furyCell/tests/'
                          . md5($this->curCmd->getUrl()) . '.html';
                file_put_contents($fileName, $content);
            }

            if (is_array($return)) {
                $this->addToPlan($return);
            }
            if (is_a($return, '\\Tarlag\\Mollicute\\Command')) {
                $this->add($return);
            }
        } while (!empty($this->plan));
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
