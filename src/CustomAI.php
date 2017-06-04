<?php

namespace Informagenie;

class CustomAI
{
    /**
     * Valeur par defaut de la masque
     *
     * @const DEFAULT_MASK
     */
    const DEFAULT_MASK = '2018(0000)';

    /**
     * La partie constante de $mask
     *
     * @var mixed
     */
    static protected $constant;

    /**
     * La colonne clé primaire $table
     *
     * @var String
     */
    static protected $column;

    /**
     * La table
     *
     * @var String
     */
    static protected $table;

    /**
     * L'instance de la connexion dans la base de données
     *
     * @var \PDO
     */
    static protected $dsn;

    /**
     * La masque de la valeur d'auto incremente
     *
     * @var String
     */
    static public $mask;


    function __construct(\PDO $dsn, $table, $column = 'id')
    {
        static::init($dsn, $table, $column);
    }

    /**
     * Initialise les parametres
     *
     * @param Array | String $options
     * @throws \Exception
     */
    static function init()
    {
        $args = func_get_args();
        if (1 == sizeof($args) AND is_array($args[0])) {
            $options = $args[0];
            foreach ($options as $index => $option) {
                if (property_exists(static::class, $index)) {
                    $method = 'set' . ucfirst($index);
                    call_user_func(array(static::class, $method), $option);
                }
            }
        } elseif (sizeof($args) >= 2) {
            static::setDsn($args[0]);
            static::setTable($args[1]);
            static::setColumn($args[2]);
        } else {
            throw new \Exception('Les arguments ' . print_r($args, true) . 'sonts invalides ou insuffisant');
        }
        static::default_params();
    }


    /**
     * Comutateur de $dsn
     *
     * @param \PDO $dsn
     * @throws \Exception
     * @return NULL
     */
    public function setDsn(\PDO $dsn)
    {
        static::$dsn = $dsn;
        static::$dsn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Comutateur de $table
     *
     * @param $table
     * @return NULL
     */
    public function setTable($table)
    {
        static::$table = !empty($table) ? $table : 'table';
    }

    /**
     * Comutateur de $column
     *
     * @param String $column
     * @return NULL
     */
    public function setColumn($column)
    {
        static::$column = !empty($column) ? $column : 'id';
    }

    /**
     * Initialise les valeurs par défault
     *
     */
    protected function default_params()
    {
        static::$mask = (empty(static::$mask)) ? static::DEFAULT_MASK : static::$mask;
        static::$constant = static::constant();
        static::$column = (empty(static::$column)) ? 'id' : static::$column;
    }

    /**
     * Retourne la partie constante de la masque $mask
     *
     * @param string $mask
     * @return string
     */
    protected function constant($mask = "")
    {
        $mask = (empty($mask)) ? static::$mask : $mask;
        preg_match("#(.{0,})\(#i", $mask, $match);
        if (!isset($match[1])) {
            throw new \Exception("La masque doit respecter les normes suivantes : CCCC...(XXXX...) ou (XXXX...) celle dans la base de données est différente de celle ci");
        }
        return $match[1];
    }

    /**
     * Initialise la classe sans instanciation
     *
     * @param Array $options
     * @throws \Exception
     */
    public static function create($options)
    {
        static::init($options);
        return static::auto_increment();
    }

    /**
     * Retourne l'id auto incrementé généré
     *
     * @return string
     * @throws \Exception
     */
    public static function auto_increment()
    {
        $data = static::fetch_last();

        if (!static::isCoherent()) {
            throw new \Exception('L\'incoréance existe entre l\'id de la base de données (' .
                static::constant($data) . ') et la masque actuelle (' . static::constant() . ')', E_USER_NOTICE);
        }

        $number = static::incrementable($data);

        return static::constant() . static::increment($number);
    }

    /**
     *
     * @return string
     * @throws \Exception
     */
    public function __toString()
    {
        return static::auto_increment();
    }

    /**
     * Retourne le dernier id depuis la base de donnée
     *
     * @return string
     * @throws \Exception
     */
    protected function fetch_last()
    {
        $data = static::$dsn->query("SELECT " . static::$column . " FROM " . static::$table . " ORDER BY " . static::$column . " DESC");
        if ($data) {
            $d = $data->fetchAll(\PDO::FETCH_ASSOC);
            return isset($d[0]) ? $d[0][static::$column] : static::rigth_mask();
        } else {
            throw new \Exception("La table " . static::$table . " ou la colonne " . static::$column . " semble être invalide");
        }
    }

    /**
     * Retourne la masque sans les paranthèses ()
     *
     * @return string
     */
    protected function rigth_mask()
    {
        return static::constant() . static::incrementable();
    }

    /**
     * Retourne la partie variable ou incrementable de la masque $mask ou $this->mask
     *
     * @param string $mask
     * @return string
     */
    protected static function incrementable($mask = "")
    {
        $mask = empty($mask) ? static::$mask : $mask;
        if (!preg_match("#\(.{0,}\)#i", $mask)) {
            return (int)substr($mask, strlen($mask) - strlen(static::incrementable(static::$mask)));
        } else {
            preg_match("#(\(.{0,})\)#i", $mask, $match);
            $incrementable = ltrim($match[1], '() ');
            return $incrementable;
        }

    }

    /**
     * Vérifie si la masque de l'instance et de la base de données sont cohérentes
     *
     * @return bool
     * @throws \Exception
     */
    protected static function isCoherent()
    {
        return
            (bool)(strlen(static::rigth_mask()) >= strlen(static::fetch_last()) &&
                preg_match("#^" . static::constant() . "#i", static::fetch_last()));
    }

    /**
     * Incremente le numero $number
     *
     * @param $number
     * @return string
     */
    protected function increment($number)
    {
        $total = strlen($number);
        $number++;

        return static::normalize($number);
    }

    /**
     * Format le numéro $numero
     *
     * @param $number
     * @return string
     */
    protected function normalize($number)
    {
        $lenght_mask = strlen(static::incrementable(static::$mask));
        while (strlen($number) < $lenght_mask) {
            $number = (int)0 . $number;
        }

        return $number;
    }
}
