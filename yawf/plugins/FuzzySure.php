<?php
/**
 * FuzzySure -- Simple User-defined Rule Engine (SURE) - Fuzzy logic version
 *
 * "FuzzySure" is a simple way to embed a fuzzy expert system in your PHP web
 * application. It subclasses the "Sure" expert system classes to add fuzziness.
 * Here's a simple example:
 *
 * <code>
 * $sure = new FuzzySure();
 * $sure->with('rules.fuzzy')->given('facts.fuzzy')->infer();
 * </code>
 * 
 * ...where "rules.fuzzy" and "facts.fuzzy" are the filenames for files written
 * like this:
 *
 * rules.fuzzy:
 * <code>
 * rule: Relax when it is hot, also on holidays
 * when: $is_a_hot_day or $is_holiday
 * then: if ($TRUTH >= 0.5) print "Relaxing today, thanks!"
 * // Note that you can use the $TRUTH variable to read fuzzy truths like this
 *
 * rule: Is it hot today?
 * when: $today->is($hot)
 * then: $is_a_hot_day = $TRUTH
 * </code>
 *
 * facts.fuzzy:
 * <code>
 * $today = new FuzzyObject(array('temp' => 85))
 * $hot = new FuzzyMatch('temp', array(80, 90, 100, 110)
 * </code>
 *
 * The syntax for the rules and the facts is regular PHP code, except that you
 * may also use FuzzyObject and FuzzyMatch objects. The rules should include
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
 * $sure = new FuzzySure();
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
 * $sure = new FuzzySure();
 * $sure->limit(1); // only match the rules against the facts once
 * </code>
 * ...or just pass the iteration limit to the constructor like this:
 * <code>
 * $sure = new FuzzySure(1);
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

load_plugin('Sure');

/**
 * A class to provide a Simpe User-defined Rule Engine (SURE) with fuzzy logic
 * @package Sure
 */
class FuzzySure extends Sure
{
    // Protected functions

    /**
     * Create a new fuzzy parser (overridden from the Sure base class)
     * @return FuzzyParser
     */
    protected function create_parser()
    {
        return new FuzzyParser();
    }
}

/**
 * A class to parse fuzzy rules and facts as pseudo-PHP code
 * @package Sure
 */
class FuzzyParser extends SureParser
{
    // Protected functions

    /**
     * Create a new fuzzy rule (overridden from the SureParser base class)
     * @param string $name The name of the rule
     * @return FuzzyRule
     */
    protected function create_rule($name)
    {
        return new FuzzyRule($name);
    }
}

/**
 * A fuzzy rule has a name, a list of conditions and a list of actions to take
 * @package Sure
 */
class FuzzyRule extends SureRule
{
    /**
     * Match this rule's conditions against the facts held in a memory object
     * @param Object &$memory The memory object with facts to match against
     * @global integer &$memory->TRUTH (available just as $TRUTH in your rules)
     */
    public function match(&$memory)
    {
        $truth = NULL;
        foreach ($this->conditions as $cond)
        {
            $regexp_or = '/\s+(or|\|\|)\s+/';
            if (preg_match($regexp_or, $cond))
            {
                $or_conditions = preg_split($regexp_or, $cond);
                $truth = NULL;
                foreach ($or_conditions as $or_cond)
                {
                    $value = $this->match_condition($or_cond, $memory);
                    if (is_null($truth) || $truth < $value) $truth = $value;
                }
            }
            else // regular condition
            {
                $value = $this->match_condition($cond, $memory);
                if (is_null($truth) || $truth > $value) $truth = $value;
            }
        }
        $memory->TRUTH = $truth;
        return $truth;
    }
}

/**
 * A FuzzyObject is just like a regular Object except it has an "is()" method
 * @package Sure
 */
class FuzzyObject extends Object
{
    /**
     * Return to what degree this fuzzy object matches with a FuzzyMatch object
     * @param FuzzyMatch $match A fuzzy match object you've declared in rules
     * @return float
     */
    public function is($match)
    {
        return $match->truth($this);
    }
}

/**
 * A FuzzyMatch object matches an object's property against a fuzzy curve: _/\_
 * @package Sure
 */
class FuzzyMatch
{
    private $field, $match;

    /**
     * Create a new fuzzy match object
     * @param string $field The field to be matched on fuzzy objects
     * @param string $match Either a single number or an array of (a, b, c, d)
     */
    public function __construct($field, $match)
    {
        $this->field = $field;
        $this->match = $match;
    }

    /**
     * Return the degree of truth when we are matched with an object field
     * @param Object &$object An object with a field to match
     * @return float
     */
    public function truth($object)
    {
        $field = $this->field;
        if (!property_exists($object, $field)) return 0.0;
        $value = $object->$field;

        return is_array($this->match) ? $this->fuzzy_match($value)
                                      : ($value == $this->match ? 1.0 : 0.0);
    }

    /**
     * Return the degree of truth when we are matched with a field value
     * @param number &$value The value of an object's field
     * @return float
     */
    private function fuzzy_match($value)
    {
        list ($a, $b, $c, $d) = $this->match;
        if ($a >= $value || $d <= $value) return 0.0;
        if ($b <= $value && $c >= $value) return 1.0;
        if ($a < $value && $value < $b) return $this->fuzzy_truth($value, $a, $b);
        if ($c < $value && $value < $d) return $this->fuzzy_truth($value, $d, $c);
    }

    /**
     * Return the degree of truth when we match a value between two bounds
     * @param number &$value The y-axis value of an object's field
     * @param number &$zero The y-axis value where the x-axis is zero
     * @param number &$one The y-axis value where the x-axis is one
     * @return float
     */
    private function fuzzy_truth($value, $zero, $one)
    {
        $value = (float)$value;
        $zero = (float)$zero;
        $one = (float)$one;
        return ($value - $zero) / ($one - $zero);
    }
}

// End of FuzzySure.php
