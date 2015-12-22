<?php

namespace Siwayll\Mollicute\Tests\Units;

use atoum;
use Siwayll\Mollicute\Command as TestClass;

/**
 * Test unitaire pour Command
 */
class Command extends atoum
{
    /**
     * Création simple d'une Commande
     *
     * @return void
     */
    public function testConstruct()
    {
        $this
            ->object(new TestClass())
            ->assert('Spécification à postériori de l\'url')
                ->given($command = new TestClass())
                ->variable($command->getUrl())
                    ->isNull()

            ->assert('Spécification directe de l\'url')
                ->given($command = new TestClass('toto'))
                ->string($command->getUrl())
                    ->isEqualTo('toto')
        ;
    }

    /**
     * Gestion du temps de temporisation
     */
    public function testSleep()
    {
        $this
            ->given($command = new TestClass('toto'))
            ->boolean($command->getSleep())
                ->isFalse()

            ->object($command->setSleep(5))
                ->isIdenticalTo($command)
            ->integer($command->getSleep())
                ->isEqualTo(5)
        ;
    }

    /**
     * Paramétrage de Curl
     *
     * @throws Exception
     */
    public function testCurlConfig()
    {
        $this
            ->given($command = new TestClass('toto'))

            ->assert('Définition d\'une option curl')
                ->array($command->getCurlOpts())
                    ->notHasKey(CURLOPT_VERBOSE)
                ->object($command->setCurlOpt(CURLOPT_VERBOSE, true))
                    ->isIdenticalTo($command)
                ->array($command->getCurlOpts())
                    ->boolean[CURLOPT_VERBOSE]->isTrue()
                ->boolean($command->getCurlOpt(CURLOPT_VERBOSE))
                    ->isTrue()

            ->assert('Réécriture d\'une option curl')
                ->object($command->setCurlOpt(CURLOPT_VERBOSE, false))
                    ->isIdenticalTo($command)
                ->array($command->getCurlOpts())
                    ->boolean[CURLOPT_VERBOSE]->isFalse()
                ->boolean($command->getCurlOpt(CURLOPT_VERBOSE))
                    ->isFalse()

            ->assert('Demande d\'information non présente')
                ->exception(function () use ($command) {
                    $command->getCurlOpt(CURLOPT_BINARYTRANSFER);
                })
                    ->hasMessage('Aucun attribut curl du nom de __' . CURLOPT_BINARYTRANSFER . '__')
        ;
    }
}
