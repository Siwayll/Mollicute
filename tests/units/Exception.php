<?php
/**
 * Test class for Exception.
 *
 * @author  Siwaÿll <sana.th.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace tests\unit\Siwayll\Mollicute;

use atoum;

/**
 * Test class for Exception.
 *
 * @author  Siwaÿll <sana.th.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Exception extends atoum
{

    public function testClass()
    {
        $this
            ->object($exception = new \Siwayll\Helion\Exception())
                ->isInstanceOf('\Exception')
                ->isNotCallable()
        ;
    }
}

