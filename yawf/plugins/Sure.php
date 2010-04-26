<?php
/**
   * Sure -- Simple User-defined Rule Engine (SURE)
   * @version 0.1
   * @author Kevin Hutchinson <kevin@guanoo.org>
   * @link http://github.com/hutchike/YAWF
   * @copyright Copyright 2010 Kevin Hutchinson
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package Sure
   */
class Sure
{
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

    public function match()
    {
        // Remember facts into memory

        $memory = new Object();
        foreach ($this->parsed_facts as $fact)
        {
            $fact->remember($memory);
        }

        // Fire all rules that match

        foreach ($this->parsed_rules as $rule)
        {
            if ($rule->match($memory)) $rule->fire($memory);
        }

        $this->memory = $memory;
        return $this;
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
            if (!$line) continue;

            if (preg_match('/^rule:/i', $line))
            {
                if ($rule) array_push($rules, $rule);
                $rule = new SureRule();
                continue;
            }
            elseif (preg_match('/^(if|when):/i', $line))
            {
                $parsing = self::CONDITIONS;
                continue;
            }
            elseif (preg_match('/^then:/i', $line))
            {
                $parsing = self::ACTIONS;
                continue;
            }

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
    private $conditions;
    private $actions;

    public function __construct()
    {
        $this->conditions = array();
        $this->actions = array();
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
            $cond = preg_replace('/(.*)([<>])(.*)/', '($1)$2($3)', $cond);
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

// End of Sure.php
