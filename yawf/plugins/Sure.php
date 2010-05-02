<?php
/**
 * Sure -- Simple User-defined Rule Engine (SURE)
 *
 * "Sure" is a simple way to embed an expert system in your PHP web application.
 * Here's a simple example:
 *
 * <code>
 * $sure = new Sure();
 * $sure->with('rules.sure')->given('facts.sure')->infer();
 * </code>
 * 
 * ...where "rules.sure" and "facts.sure" are the filenames for files written
 * like this:
 *
 * rules.sure:
 * <code>
 * rule: Relax on the weekend
 * when: $is_weekend or $is_holiday
 * then: print "I'm relaxing today, thanks"
 *
 * rule: Is it the weekend yet?
 * when: $today == 'Sat' or $today == 'Sun'
 * then: $is_weekend = TRUE
 * </code>
 *
 * facts.sure:
 * <code>
 * $today = date('D')
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
 * $sure = new Sure();
 * $sure->with('rules.sure')->given('facts.sure')->infer(array('bank' => $bank_object));
 * </code>
 *
 * This way, the Sure rules engine can be used to orchestrate your other PHP
 * code according to flexible rules. Note that the filenames "rules.sure" and
 * "facts.sure" may be replaced with string values if you prefer to pass the
 * rules and facts directly, for example if you store them in a database.
 *
 * The "infer()" method will iterate up to 1000 times until the state of the
 * facts memory is unchaged. If you need to iterate more or less than 1000
 * times then call the "limit()" getter/setter method like this:
 * <code>
 * $sure = new Sure();
 * $sure->limit(1); // only match the rules against the facts once
 * </code>
 * ...or just pass the iteration limit to the constructor like this:
 * <code>
 * $sure = new Sure(1);
 * </code>
 * ...but this is less readable code, so a call to "limit()" is preferred.
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
    const MAX_INFERENCE_LIMIT = 1000;

    private $limit;
    private $memory;
    private $parser;
    private $parsed_rules;
    private $parsed_facts;

    /**
     * Create a new Sure object to infer from rules and facts
     */
    public function __construct($limit = self::MAX_INFERENCE_LIMIT)
    {
        $this->limit($limit);
        $this->memory = NULL;
        $this->parser = $this->create_parser();
        $this->parsed_rules = array();
        $this->parsed_facts = array();
    }

    /**
     * Get/set the maximum number of iterations allowed when <var>infer()</var>
     * is called to make inferences from the rules and facts.
     * @param integer $limit The maximum number of iterations allowed (optional)
     * @return integer
     */
    public function limit($limit = NULL)
    {
        if (!is_null($limit)) $this->limit = $limit;
        return $this->limit;
    }

    /**
     * Provide a rules file or a string of rules to be parsed by this object
     * @param string $rules A filename or a string of rules code in pseudo-PHP
     * @return Sure
     */
    public function with($rules)
    {
        $rules = $this->read($rules);
        $this->parsed_rules = $this->parser->parse_rules($rules);
        return $this;
    }

    /**
     * Provide a facts file or a string of facts to be parsed by this object
     * @param string $facts A filename or a string of facts code in pseudo-PHP
     * @return Sure
     */
    public function given($facts)
    {
        $facts = $this->read($facts);
        $this->parsed_facts = $this->parser->parse_facts($facts);
        return $this;
    }

    /**
     * Infer new facts from our original facts, given our rules, and iterating
     * no more than <var>$this->limit</var> times.
     * @param array $data An optional array of fact data to mix with other facts
     * @return Sure
     */
    public function infer($data = NULL)
    {
        // Remember facts into memory

        $memory = new SureMemory($data);
        foreach ($this->parsed_facts as $fact)
        {
            $fact->remember($memory);
        }

        // Infer while memory changes

        $change = FALSE;
        $repeat = $self->limit;
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

    /**
     * Return the memory object that holds the current state of all the facts
     * @return SureMemory
     */
    public function memory()
    {
        return $this->memory;
    }

    // Protected functions

    /**
     * Create a new parser
     * @return SureParser
     */
    protected function create_parser()
    {
        return new SureParser();
    }

    // Private functions

    /**
     * Read a file if the file exists, or otherwise assume it's a literal string
     * @param string $file The filename or a literal string of pseudo-PHP code
     * @return string
     */
    private function read($file)
    {
        return file_exists($file) ? file_get_contents($file) : $file;
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

    /**
     * Parse a string of rules by returning an array of rule objects
     * @param string $rules A string of rules to be parsed (see formatting guide)
     * @return array
     */
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
                $rule = $this->create_rule($name);
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

    /**
     * Parse a string of facts by returning an array of fact objects
     * @param string $facts A string of facts to be parsed, listed one per line
     * @return array
     */
    public function parse_facts($facts)
    {
        $lines = preg_split("/\r?\n/", $facts);
        $facts = array();
        foreach ($lines as $line)
        {
            $line = $this->trim($line);
            if (!$line) continue;

            array_push($facts, $this->create_fact($line));
        }
        return $facts;
    }

    // Protected functions

    /**
     * Create a new rule
     * @param string $name The name of the rule
     * @return SureRule
     */
    protected function create_rule($name)
    {
        return new SureRule($name);
    }

    /**
     * Create a new fact
     * @param string $line The fact written as a line of pseudo-PHP
     * @return SureFact
     */
    protected function create_fact($line)
    {
        return new SureFact($line);
    }

    // Private functions

    /**
     * Trim a line by removing whitespace and comments
     * @param string $line A line of pseudo-PHP code
     * @return string
     */
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
 * when all the conditions are met
 * @package Sure
 */
class SureRule
{
    private $name;
    private $conditions;
    private $actions;

    /**
     * Create a new rule object with a name
     * @param string $name The name of the rule
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->conditions = array();
        $this->actions = array();
    }

    /**
     * Get the name of this rule, as specified in its "rule: name" definition
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Add a condition to this rule
     * @param string $condition A condition to add, written as pseudo-PHP code
     */
    public function condition($condition)
    {
        array_push($this->conditions, $condition);
    }

    /**
     * Add an action to this rule
     * @param string $action An action to add, written as pseudo-PHP code
     */
    public function action($action)
    {
        array_push($this->actions, $action);
    }

    /**
     * Match this rule's conditions against the facts held in a memory object
     * @param SureMemory &$memory The memory object with facts to match against
     */
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

    /**
     * Fire this rule's actions by eval'ing them as pseudo-PHP code
     * @param SureMemory &$memory The memory object with facts to match against
     */
    public function fire(&$memory)
    {
        foreach ($this->actions as $action)
        {
            $action = preg_replace('/\$(\w+)/', '$memory->$1', $action);
            eval("$action;");
        }
    }

    // Private functions

    /**
     * Match a condition according to the state of the facts in a memory object
     * @param string $cond A condition written as pseudo-PHP code
     * @param SureMemory &$memory The memory object with facts to match against
     * @return boolean
     */
    private function match_condition($cond, &$memory)
    {
        if (FALSE === strpos($cond, '(')) // don't over-ride user brackets
        {
            $cond = preg_replace('/(.*\s)([<>]=?)(\s.*)/', '($1)$2($3)', $cond);
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

    /**
     * Create a new fact object with some pseudo-PHP code
     * @param string $fact Some pseudo-PHP fact definition code
     */
    public function __construct($fact)
    {
        $this->fact = $fact;
    }

    /**
     * Remember this fact in a memory object by eval'ing the pseudo-PHP code
     * @param SureMemory &$memory The memory object reference to update
     */
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
    /**
     * Create a new memory object, and optionally populate it with some data
     * @param array $data An optional array of data to initialize the object
     */
    function __construct($data = NULL)
    {
        if (!$data) $data = array();
        foreach ($data as $field => $value)
        {
            $this->$field = $value;
        }
        $this->memory = $this;
    }

    /**
     * Get a property from this memory object, and create a new memory object
     * as a side-effect if the property does not exist. This allows for chained
     * property getters like <var>$some->other->thing</var> to work without any
     * errors.
     * @param string $var The property name
     * @return various
     */
    function __get($var)
    {
        if ($this->$var === NULL) $this->$var = new SureMemory();
        return $this->$var;
    }
}

// End of Sure.php
