<?php
/**
   * Sure -- Simple User-defined Rule Engine (SURE)
   * @version 0.2
   * @author Kevin Hutchinson <kevin@guanoo.org>
   * @link http://github.com/hutchike/YAWF
   * @copyright Copyright 2010 Kevin Hutchinson
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package Sure
   */
class Sure
{
    const MAX_INFERENCE_LOOPS = 100;

    private $memory;
    private $parser;
    private $parsed_rules;
    private $parsed_facts;

    public function __construct()
    {
        $this->memory = NULL;
        $this->parser = new SureParser();
        $this->parsed_rules = array();
        $this->parsed_facts = array();
    }

    public function with($rules)
    {
        $rules = $this->read($rules);
        $this->parsed_rules = $this->parser->parse_rules($rules);
        return $this;
    }

    public function given($facts)
    {
        $facts = $this->read($facts);
        $this->parsed_facts = $this->parser->parse_facts($facts);
        return $this;
    }

    public function infer($data = NULL, $repeat = self::MAX_INFERENCE_LOOPS)
    {
        // Remember facts into memory

        $memory = new SureMemory($data);
        foreach ($this->parsed_facts as $fact)
        {
            $fact->remember($memory);
        }

        // Infer while memory changes

        $change = FALSE;
        do
        {
            // Fire all rules that match, and look for any changes

            $before = var_export($memory, TRUE);
            foreach ($this->parsed_rules as $rule)
            {
                if ($rule->match($memory)) $rule->fire($memory);
            }
            $after = var_export($memory, TRUE);
            $change = ($before != $after);
        }
        while ($change && $repeat-- && !$memory->finish);

        // Don't lose our memory

        $this->memory = $memory;
        return $this;
    }

    public function match($data = NULL)
    {
        return $self->infer($data, 1);
    }

    public function memory()
    {
        return $this->memory;
    }

    // Private functions

    private function read($file)
    {
        return file_exists($file) ? file_get_contents($file) : $file;
    }

    private function quit($message)
    {
        print "error: $message\n";
        exit;
    }
}

class SureParser
{
    const CONDITIONS = 1;
    const ACTIONS = 2;

    public function parse_rules($rules)
    {
        $lines = preg_split("/\r?\n/", $rules);
        $rules = array();
        $rule = NULL;
        foreach ($lines as $line)
        {
            $line = $this->trim($line);
            if (preg_match('/^rule:\s*(.*)/i', $line, $matches))
            {
                if ($rule) array_push($rules, $rule);
                $name = $matches[1];
                $rule = new SureRule($name);
                $line = '';
            }
            elseif (preg_match('/^(if|when):\s*(.*)/i', $line, $matches))
            {
                $parsing = self::CONDITIONS;
                $line = $matches[2];
            }
            elseif (preg_match('/^then:\s*(.*)/i', $line, $matches))
            {
                $parsing = self::ACTIONS;
                $line = $matches[1];
            }
            if (!$line) continue;

            switch ($parsing)
            {
                case self::CONDITIONS:
                    $rule->condition($line);
                    break;

                case self::ACTIONS:
                    $rule->action($line);
                    break;

                default:
                    break;
            }
        }
        if ($rule) array_push($rules, $rule);
        return $rules;
    }

    public function parse_facts($facts)
    {
        $lines = preg_split("/\r?\n/", $facts);
        $facts = array();
        foreach ($lines as $line)
        {
            $line = $this->trim($line);
            if (!$line) continue;

            array_push($facts, new SureFact($line));
        }
        return $facts;
    }

    // Private functions

    private function trim($line)
    {
        $line = trim($line);
        $regexp_comments = '/(#|\/\/).*$/';
        $line = preg_replace($regexp_comments, '', $line);
        return $line;
    }
}

class SureRule
{
    private $name;
    private $conditions;
    private $actions;

    public function __construct($name)
    {
        $this->name = $name;
        $this->conditions = array();
        $this->actions = array();
    }

    public function name()
    {
        return $this->name;
    }

    public function condition($condition)
    {
        array_push($this->conditions, $condition);
    }

    public function action($action)
    {
        array_push($this->actions, $action);
    }

    public function match(&$memory)
    {
        foreach ($this->conditions as $cond)
        {
            $regexp_or = '/\s+(or|\|\|)\s+/';
            if (preg_match($regexp_or, $cond))
            {
                $or_conditions = preg_split($regexp_or, $cond);
                $match_at_least_one_part = FALSE;
                foreach ($or_conditions as $or_cond)
                {
                    if ($this->match_condition($or_cond, $memory))
                    {
                        $match_at_least_one_part = TRUE;
                        break;
                    }
                }
                if (!$match_at_least_one_part) return FALSE;
            }
            else // regular condition
            {
                if (!$this->match_condition($cond, $memory)) return FALSE;
            }
        }
        return TRUE;
    }

    public function fire(&$memory)
    {
        foreach ($this->actions as $action)
        {
            $action = preg_replace('/\$(\w+)/', '$memory->$1', $action);
            eval("$action;");
        }
    }

    // Private functions

    private function match_condition($cond, &$memory)
    {
        if (FALSE === strpos($cond, '(')) // don't over-ride user brackets
        {
            $cond = preg_replace('/(.*\s)([<>])(\s.*)/', '($1)$2($3)', $cond);
        }

        $cond = preg_replace('/\$(\w+)/', '$memory->$1', $cond);
        eval("\$match=($cond);");
        return $match;
    }
}

class SureFact
{
    private $fact;

    public function __construct($fact)
    {
        $this->fact = $fact;
    }

    public function remember(&$memory)
    {
        $fact = preg_replace('/\$(\w+)/', '$memory->$1', $this->fact);
        eval("$fact;");
    }
}

class SureMemory
{
    function __construct($data = NULL)
    {
        if (!$data) $data = array();
        foreach ($data as $field => $value)
        {
            $this->$field = $value;
        }
    }

    function __get($var)
    {
        if ($this->$var === NULL) $this->$var = new SureMemory();
        return $this->$var;
    }
}

// End of Sure.php
