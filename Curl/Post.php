<?php

namespace Siwayll\Mollicute\Curl;

/**
 * Gestionnaire header
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Post implements \ArrayAccess
{
    private $data = [];

    /**
     * Chargement du header
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function send()
    {
        $post = [];
        foreach ($this->data as $key => $value) {
            $post[] = $key . '=' . $value;
        }

        return implode('&', $post);
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    public function get($name)
    {
        return $this->data[$name];
    }

    public function has($name)
    {
        return isset($this->data[$name]);
    }

    public function kill($name)
    {
        unset($this->data[$name]);
        return $this;
    }

    /**
     * Getter via tableau
     *
     * @param mixed $offset Nom du champ
     *
     * @return boolean
     * @uses Solire\Conf\Conf::get()
     * @ignore
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Setter via tableau
     *
     * @param mixed $offset Nom du champ
     * @param mixed $value  Valeur
     *
     * @return boolean
     * @uses Solire\Conf\Conf::set()
     * @ignore
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unsset via tableau
     *
     * @param mixed $offset Nom du champ
     *
     * @return void
     * @uses Solire\Conf\Conf::kill()
     * @ignore
     */
    public function offsetUnset($offset)
    {
        $this->kill($offset);
    }

    /**
     * Isset via tableau
     *
     * @param mixed $offset Nom du champ
     *
     * @return boolean
     * @uses Solire\Conf\Conf::exists()
     * @ignore
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }
}
