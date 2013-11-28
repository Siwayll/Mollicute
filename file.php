<?php
/**
 * Fichier aspiré
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Siwayll\Mollicute;

/**
 * Fichier aspiré
 *
 * @author  Siwaÿll <sanath.labs@gmail.com>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class File
{
    /**
     * Séparateur de données dans les noms des fichiers
     */
    const DATA_SEP = '-';

    /**
     * Extention des fichiers par défaut
     */
    const DEFAULT_EXT = 'html';

    /**
     * Chemin vers le dossier de travail du site
     *
     * @var string Chemin
     */
    public static $workDir;

    /**
     * Identifiant de l'aspiration
     *
     * @var int
     */
    protected $id;

    /**
     * Extension du fichier à aspirer
     *
     * @var string
     */
    protected $ext = self::DEFAULT_EXT;

    /**
     * Nom du fichier personnalisé
     *
     * @var string
     */
    protected $custName = '';

    /**
     * Affichage ou non de l'id d'aspiration dans le fichier
     *
     * @var boolean
     */
    protected $mark = true;

    /**
     * Affichage ou non du numéro de la page dans le nom du fichier
     *
     * @var boolean
     */
    protected $writePage = true;

    /**
     * Pagination
     *
     * @var Page
     */
    private $page = null;

    /**
     * Utilise ou non les contrôles spécifiques sur site
     *
     * @var boolean
     */
    private $control = true;

    /**
     * Dernier nom de fichier
     *
     * @var string
     */
    private $lastName = null;

    /**
     * Dernière section paramétrée
     *
     * @var string
     */
    private $lastSectionConf = null;

    /**
     * Activation de la suppression de groupe
     *
     * Tous les fichiers aspirés pendant que le groupe est actifs sont listés
     * Si un ordre de suppression est envoyé, ils seront tous supprimés.
     *
     * Pour annuler le mode groupe il faut utiliser groupKill()
     *
     *
     * @var boolean
     */
    private $group = false;

    /**
     * Enregistre le chemin vers le dossier de travail
     *
     * @param string $dir Chemin
     *
     * @return void
     */
    public static function setWorkDir($dir)
    {
        self::$workDir = $dir;
    }

    /**
     * Gestion d'une nouvelle pagination
     *
     * @param int $id Identifiant de l'aspiration
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Passer en mode pagination
     *
     * @param Page $page Module de pagination
     *
     * @return void
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Active le mode groupe
     *
     * Permet de faire de la suppression de lots de fichiers
     *
     * @return void
     */
    public function groupStart()
    {
        $this->group = true;
        return $this;
    }

    /**
     * Arrête le mode groupe et vide la liste des fichiers
     *
     * @return void
     */
    public function groupKill()
    {
        $this->groupList = array();
        $this->group = false;
        return $this;
    }

    /**
     * Renvois le nom du fichier
     *
     * @return string Chemin vers le fichier
     */
    public function getName()
    {
        $name = self::$workDir . DS;

        if ($this->mark === true) {
            $name .= $this->id;
        }

        if (!empty($this->page) && $this->writePage === true) {
            $name .= self::DATA_SEP . $this->page->getIndex();
        }

        if (!empty($this->custName)) {
            if ($this->sepaCust === true) {
                $name .= self::DATA_SEP;
                $this->sepaCust = false;
            }
            $name .= $this->custName;
            $this->custName = null;
        }

        $name .= '.' . $this->ext;

        $this->ext = self::DEFAULT_EXT;

        if ($this->group === true) {
            $this->groupList[] = $name;
        }

        $this->lastName = $name;

        return $name;
    }

    /**
     * Change l'extension du fichier utilisé pour le
     *
     * @param string $ext Extension du fichier
     *
     * @return self
     */
    public function setExtension($ext)
    {
        $this->ext = $ext;
        return $this;
    }

    /**
     * Ajout d'une section avec une chaine personnalisé dans le nom du fichier
     *
     * @param string $string chaine à ajouter
     *
     * @return self
     */
    public function setCustName($string)
    {
        $this->custName = (string) $string;

        $this->lastSectionConf = 'cust';
        return $this;
    }

    /**
     * Ajout d'un séparateur devant la section demandée
     *
     * @param string $section Nom de la section à spérarer par le séparateur
     * Si rien n'est mis, c'est dernière section paramétrée qui sera utilisée.
     *
     * @return self
     */
    public function addSepa($section = false)
    {
        if ($section === false) {
            $this->{'sepa' . ucfirst($this->lastSectionConf)} = true;
            return $this;
        }

        $this->sepaCust = true;
        return $this;
    }

    /**
     * Supprime l'écriture de l'id d'aspiration dans le nom du fichier
     *
     * @return self
     */
    public function disableId()
    {
        $this->mark = false;
        return $this;
    }

    /**
     * Supprime l'écriture de l'index de pagination
     *
     * @return self
     */
    public function disablePage()
    {
        $this->writePage = false;
        return $this;
    }

    /**
     * Renvois le nom du dernier fichier utilisé
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Désactive le contrôle spécifique au site
     *
     * @return self
     */
    public function disableSiteControl()
    {
        $this->control = false;
        return $this;
    }

    /**
     * Indique si l'utilisation des contrôles spécifiques au site
     * doivent être utilisés.
     *
     * @return boolean
     */
    public function isEnableSiteControle()
    {
        $return = $this->control;
        $this->control = true;
        return $return;
    }

    /**
     * Renvois un nom de fichier custom sous la forme "<idAsp>-$name" si $mark
     * est à true, "$name" sinon
     *
     * @param string  $name Nom du fichier + extension
     * @param boolean $mark Marquer le fichier avec l'id de l'aspi
     *
     * @return string Chemin vers le fichier
     * @deprecated utiliser setCustName à la place
     */
    public function getCustName($name, $mark = true)
    {
        $path = self::$workDir . DS;
        if ($mark === true) {
            $path .= $this->id . self::DATA_SEP;
        }
        $path .= $name;

        if ($this->group === true) {
            $this->groupList[] = $path;
        }

        $this->lastName = $path;

        return $path;
    }

    /**
     * Renvois le contenu du fichier
     *
     * @return string Contenu du fichier
     */
    public function get()
    {
        return file_get_contents($this->lastName);
    }

    /**
     * Ajoute des informations à la fin du fichier
     *
     * Le mode supplémentaire permet de préfixer l'information par un |
     * si il n'est pas activé, un saut de ligne sera ajouté à l'information
     *
     * @param string  $info information
     * @param boolean $supp Activer le mode "supplémentaire"
     *
     * @return void
     */
    public function write($info, $supp = true)
    {
        if ($supp === true) {
            $info = '|' . $info;
        } else {
            $info = "\r" . $info;
        }
        file_put_contents($this->lastName, $info, FILE_APPEND);
    }

    /**
     * Remplace le contenu du fichier
     *
     * @param string $str contenu à mettre dans le fichier
     *
     * @return void
     */
    public function replace($str)
    {
        file_put_contents($this->lastName, $str);
    }

    /**
     * Supprime le fichier
     *
     * Si le mode groupe est activé, tous les fichiers du groupe seront
     * supprimés.
     *
     * @return void
     */
    public function remove()
    {
        if ($this->group === true) {
            foreach ($this->groupList as $fileName) {
                if (file_exists($fileName)) {
                    unlink($fileName);
                }
            }
        }

        if (file_exists($this->lastName)) {
            unlink($this->lastName);
        }
    }
}
