<?php
namespace Informagenie;

class CustomAI
{

    const DEFAULT_MASK = '2018(0000000)';


    static public $constant;
    static public $column;
    static public $table;
    static public $dsn;
    static public $mask;


    function __construct(\PDO $dsn, $table, $column = 'id')
    {
        static::init($dsn, $table, $column);
    }

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

    private function setDsn($dsn)
    {
        if (!$dsn instanceof \PDO) {
            throw new \Exception('dsn doit être affecté d\'une valeur de l\'instance PDO. ' . gettype($dsn) . ' est le type de la variable donnée');
        }
        static::$dsn = $dsn;
    }

    private function setTable($table)
    {
        static::$table = !empty($table) ? $table : 'table';
    }

    private function setColumn($column)
    {
        static::$column = !empty($column) ? $column : 'id';
    }

    protected function default_params()
    {
        static::$mask = (empty(static::$mask)) ? static::DEFAULT_MASK : static::$mask;
        static::$constant = static::constant();
    }

    public function constant($mask = "")
    {
        $mask = (empty($mask)) ? static::$mask : $mask;
        return trim(strstr($mask, static::incrementable($mask), true), '() ');
    }

    public static function incrementable($mask = "")
    {
        $mask = empty($mask) ? static::$mask : $mask;

        if (!preg_match("#\(.+\)#i", $mask)) {
            return substr($mask, strlen($mask) - strlen(static::incrementable(static::$mask)));
        } else {
            preg_match("#(\(.+)\)#i", $mask, $match);
            $incrementable = ltrim($match[1], '() ');
            return $incrementable;
        }

    }

    /**
     * Static methode
     */

    static function create($options)
    {
        static::init($options);
    }

    static function id()
    {
        return static::generate();
    }

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

    protected
    function rigth_mask()
    {
        return static::constant() . static::incrementable();
    }

    static function isCoherent()
    {
        return (bool)preg_match("#^" . static::constant() . "#i", static::fetch_last());
    }

    protected
    function increment($number)
    {
        $total = strlen($number);
        $number++;

        return static::normalize($number);
    }

    protected
    function normalize($number)
    {
        $lenght_mask = strlen(static::incrementable(static::$mask));
        while (strlen($number) < $lenght_mask) {
            $number = (int)0 . $number;
        }

        return $number;
    }
}