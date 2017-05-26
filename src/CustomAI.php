<?php
namespace Informagenie;

class CustomAI
{
    protected $constant;
    protected $column;
    protected $table;
    protected $dsn;
    protected $mask;


    function __construct(\PDO $dsn, $table, $column = 'id')
    {
        $this->default_params();
        $this->dsn = $dsn;
        $this->table = $table;
        $this->column = $column;
    }

    protected function default_params()
    {
        $this->mask = date('Y') . '(000)';
        $this->constant = trim(strstr($this->mask, $this->incrementable($this->mask), true), '() ');
    }

    protected function incrementable($mask)
    {
        $mask = empty($mask) ? $this->mask : $mask;
        if (!preg_match("#\(.+\)#i", $mask)) {
            return substr($mask, strlen($mask) - strlen($this->incrementable($this->mask)));
        } else {
            preg_match("#(\(.+)\)#i", $mask, $match);
            return ltrim($match[1], '() ');
        }

    }

    function generate()
    {
        $data = $this->fetch_last();
        if (empty($data)) {
            return $this->mask;
        } else {
            $number = $this->incrementable($data);
            return (int)$this->constant . $this->increment($number);
        }
    }

    protected function fetch_last()
    {
        $data = $this->dsn->query("SELECT {$this->column} FROM {$this->table} ORDER BY {$this->column} DESC");
        if ($data) {
            $d = $data->fetchAll(\PDO::FETCH_ASSOC);
            return isset($d[0]) ? $d[0][$this->column] : $this->mask;
        } else {
            throw new Exception("La table $this->table ou la colonne $this->column semble être invalide");
        }
    }

    protected function increment($number)
    {
        $total = strlen($number);
        $number++;

        return $this->normalize($number);
    }

    protected function normalize($number)
    {
        $lenght_mask = strlen($this->incrementable($this->mask));
        while (strlen($number) < $lenght_mask) {
            $number = (int)0 . $number;
        }

        return $number;
    }


    /**
     * Static methode
     */

    static function create($options)
    {
        foreach($options as $item=>$option)
        {
            self::$item = $option;
        }
    }

    static function test($data)
    {
        self::generate();
    }

}