<?php
/**
 * SureFuzzy -- Simple User-defined Rule Engine (SURE) - Fuzzy logic version
 *
 * "SureFuzzy" is a simple way to embed a fuzzy  expert system in your PHP web
 * application. It subclasses the "Sure" hard-logic expert system classes.
 * Here's a simple example:
 *
 * <code>
 * $sure = new SureFuzzy();
 * $sure->with('rules.fuzzy')->given('facts.fuzzy')->infer();
 * </code>
 * 
 * ...where "rules.fuzzy" and "facts.fuzzy" are the filenames for files written
 * like this:
 *
 * rules.fuzzy:
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
 * facts.fuzzy:
 * <code>
 * $today = date('D')
 * </code>
 *
 * The syntax for the rules and the facts is regular PHP code, except that you
 * TODO: Explain the fuzzy stuff ... and the rules should include
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
 * @package SureFuzzy
 */

load_plugin('Sure');

/**
 * A class to provide a Simpe User-defined Rule Engine (SURE)
 * @package SureFuzzy
 */
class SureFuzzy extends Sure
{
    // Protected functions

    /**
     * Create a new fuzzy parser
     * @return SureFuzzyParser
     */
    protected function create_parser()
    {
        return new SureFuzzyParser();
    }
}

/**
 * A class to parse rules and facts as PHP code
 * @package SureFuzzy
 */
class SureFuzzyParser extends SureParser
{
    // Protected functions

    /**
     * Create a new fuzzy rule
     * @param string $name The name of the rule
     * @return SureFuzzyRule
     */
    protected function create_rule($name)
    {
        return new SureFuzzyRule($name);
    }

    /**
     * Create a new fuzzy fact
     * @param string $line The fact written as a line of pseudo-PHP
     * @return SureFuzzyFact
     */
    protected function create_fact($line)
    {
        return new SureFuzzyFact($line);
    }
}

/**
 * A fuzzy rule has a name, a list of conditions and a list of actions to take
 * when all the conditions are met
 * @package SureFuzzy
 */
class SureFuzzyRule extends SureRule
{
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
}

/**
 * A fuzzy fact is defined by a line of pseudo-PHP code
 * @package SureFuzzy
 */
class SureFuzzyFact extends SureFact
{
}

class SureThing
{
    public $is;

    public function __constrct(&$memory)
    {
        $this->memory = $memory;
        $this->is = new SureFuzzySet($memory);
    }
}

class SureFuzzySet
{
    private $memory;
    private $memberships;

    public function __construct(&$memory)
    {
        $this->memory = $memory;
    }

    public function __set()
    {
        // TODO
    }

    public function __get()
    {
        // TODO
    }
}

class SureMatch
{
    private $var, $a, $b, $c, $d;

    public function __construct($var, $a, $b, $c, $d)
    {
    }
}

// End of SureFuzzy.php
