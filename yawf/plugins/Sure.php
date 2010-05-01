<?php
/**
 * Sure -- Simple User-defined Rule Engine (SURE)
 *
 * "Sure" is a simple way to embed an expert system in your PHP web application.
 * Here's a simple example:
 *
 * $sure = new Sure();
 * $sure->with('rules.sure')->given('facts.sure')->infer();
 * 
 * ...where "rules.sure" and "facts.sure" are files written like this:
 *
 * rules.sure:
 * <code>
 *   rule: Relax on the weekend
 *   when: $today == 'Sat' or $today == 'Sun'
 *   then: print "I'm relaxing today, thanks"
 * </code>
 *
 * facts.sure:
 * <code>
 *   $today = date('D')
 * </code>
 *
 * The syntax for the rules and the facts is regular PHP code, except that you
 * may write "or" instead of "||" for readability and the rules should include
 * "rule:", "when:" and "then:" prefix labels at the start of lines to define
 * the rules. (You can replace "when:" with "if:" if you prefer).
 *
 * If you want a rule to meet many conditions, you can list them on lines like
 * this:
 *
 * <code>
 * rule: Pay rent on 1st of the month
 * when:
 *   $day_of_month == 1
 *   $rent_unpaid
 *   $cash_in_pocket >= 4800
 * then:
 *   $bank->pay_the_rent()
 * </code>
 *
 * In this example, we've used a PHP object $bank, which may be passed into the
 * Sure object constructor like this:
 *
 * <code>
 * $sure = new Sure(array('bank' => $bank_object));
 * $sure->with('rules.sure')->given('facts.sure')->infer();
 * </code>
 *
 * This way, the Sure rules engine can be used to orchestrate your other PHP
 * code according to flexible rules. Finally, the filenames "rules.sure" and
 * "facts.sure" may be replaced with string values if you prefer to pass the
 * rules and facts directly, for example if you store them in a database.
 *
 * @version 0.2
 * @author Kevin Hutchinson <kevin@guanoo.org>
 * @link http://github.com/hutchike/YAWF
 * @copyright Copyright 2010 Kevin Hutchinson
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package Sure
 */

/**
 * A class to provide a Simpe User-defined Rule Engine (SURE)
 * @package Sure
 */
class Sure
{
    /**
     * Limit the maximum number of inference iterations through the rules
     */
    const MAX_INFERENCE_LOOPS = 100;

    private $memory;
    private $parser;
    private $parsed_rules;
    private $parsed_facts;

    /**
     * Create a new Sure object to infer from rules and facts
     */
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

/**
 * A class to parse rules and facts as PHP code
 * @package Sure
 */
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

/**
 * A rule has a name, a list of conditions and a list of actions to take
 * when all the conditions are met.
 * @package Sure
 */
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

/**
 * A fact may be remembered into a memory variable (see SureMemory)
 * @package Sure
 */
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

/**
 * A class to remember facts in a simple object hierarchy
 * @package Sure
 */
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
