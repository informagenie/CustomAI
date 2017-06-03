<?php

namespace Informagenie;

class CustomAI
{
    /**
     * Default value of mask
     *
     * @const DEFAULT_MASK
     */
    const DEFAULT_MASK = '2018(0000)';

    /**
     * Unvariable value of mask
     *
     * @var mixed
     */
    static protected $constant;

    /**
     * A primary key column name
     *
     * @var String
     */
    static protected $column;

    /**
     * A table
     *
     * @var String
     */
    static protected $table;

    /**
     * Instance of connexion to database
     *
     * @var \PDO
     */
    static protected $dsn;

    /**
     * A mask of auto increment value
     *
     * @var String
     */
    static public $mask;


    function __construct(\PDO $dsn, $table, $column = 'id')
    {
        static::init($dsn, $table, $column);
    }

    /**
     * Initialize all parameters
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
     * Setter of $dsn
     *
     * @param \PDO $dsn
     * @throws \Exception
     * @return NULL
     */
    public function setDsn($dsn)
    {
        if (!$dsn instanceof \PDO) {
            throw new \Exception('dsn doit être affecté d\'une valeur de l\'instance PDO. ' . gettype($dsn) . ' est le type de la variable donnée');
        }
        static::$dsn = $dsn;
        static::$dsn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Setter of $table
     *
     * @param $table
     * @return NULL
     */
    public function setTable($table)
    {
        static::$table = !empty($table) ? $table : 'table';
    }

    /**
     * Setter of $column
     *
     * @param String $column
     * @return NULL
     */
    public function setColumn($column)
    {
        static::$column = !empty($column) ? $column : 'id';
    }

    /**
     * Set default value of attributes
     *
     */
    protected function default_params()
    {
        static::$mask = (empty(static::$mask)) ? static::DEFAULT_MASK : static::$mask;
        static::$constant = static::constant();
        static::$column = (empty(static::$column)) ? 'id' : static::$column;
    }

    /**
     * Retrieve a unvariable value of a mask
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
     * Create staticaly a instance
     *
     * @param $options
     * @throws \Exception
     */
    static function create($options)
    {
        static::init($options);
    }

    /**
     * Retrieve staticaly an id
     *
     * @return string
     * @throws \Exception
     */
    static function id()
    {
        return static::generate();
    }

    /**
     * Retrieve an id
     *
     * @return string
     * @throws \Exception
     */
    function generate()
    {
        $data = static::fetch_last();
        $number = static::incrementable($data);
        if (!static::isCoherent()) {
            throw new \Exception('L\'incoréance existe entre l\'id de la base de données (' .
                static::constant($data) . ') et la masque actuelle (' . static::constant() . ')', E_USER_NOTICE);
        }
        return static::constant() . static::increment($number);
    }

    /**
     * fetch last id value
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
     * Retrieve a right mask whitout ()
     *
     * @return string
     */
    protected function rigth_mask()
    {
        return static::constant() . static::incrementable();
    }

    /**
     * Retrieve a variable value of a mask
     *
     * @param string $mask
     * @return string
     */
    protected static function incrementable($mask = "")
    {
        $mask = empty($mask) ? static::$mask : $mask;
        if (!preg_match("#\(.{0,}\)#i", $mask)) {
            return (int) substr($mask, strlen($mask) - strlen(static::incrementable(static::$mask)));
        } else {
            preg_match("#(\(.{0,})\)#i", $mask, $match);
            $incrementable = ltrim($match[1], '() ');
            return $incrementable;
        }

    }

    /**
     * Check if database data mask is coherent with actualy mask
     *
     * @return bool
     * @throws \Exception
     */
    static function isCoherent()
    {
        return
            (bool)(strlen(static::rigth_mask()) >= strlen(static::fetch_last()) &&
                preg_match("#^" . static::constant() . "#i", static::fetch_last()));
    }

    /**
     * Increment a $number
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
     * Format number after 0
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
