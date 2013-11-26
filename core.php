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
 * Lancement d'une aspiration
 */
class Core
{
    /**
     *
     * @var array
     */
    private $plan = [];

    /**
     *
     * @param Command $start première instruction
     *
     * @return void
     * @thro
     */
    public function __construct(Command $start)
    {
        $this->plan[] = $start;
    }

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

    public function addToPlan(array $cmd)
    {
        $this->plan = array_merge($this->plan, $cmd);
    }

    public function add(Command $cmd)
    {
        $this->plan[] = $cmd;
    }
}

