<?
// Copyright (c) 2010 Guanoo, Inc.
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.

load_interfaces('Modelled', 'Validated');

/**
 * The Valid_model class extends the Simple_model class with
 * extra validation methods to validate the data field values.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Valid_model extends Simple_model implements Modelled, Validated
{
    private static $validators = array();
    private $validation_messages;

    /**
     * Create a new model object
     *
     * @param Array $data the data to initialize the object (may be an object)
     * @param Boolean $has_changed whether the new object has changed (optional)
     */
    public function __construct($data = array(), $has_changed = NULL)
    {
        $this->validation_messages = array();
        parent::__construct($data, $has_changed);
    }

    /**
     * Setup this model to validate a data field with a rule and optional args
     *
     * @param String $field the data field name to validate
     * @param String $rule the rule to check (a method name)
     * @param Array $args any optional args to pass to the rule
     */
    protected function validates($field, $rule, $args = NULL)
    {
        $table = $this->get_table();
        $key = "$table.$field";
        if (!array_key(self::$validators, $key)) self::$validators[$key] = array();
        self::$validators[$key][] = array($rule, $args);
    }

    /**
     * Return an assoc array of validation messages, keyed by model data field
     *
     * @return Array an assoc array of validation messages, keyed by data field
     */
    public function validation_messages()
    {
        return $this->validation_messages;
    }

    /**
     * Return all the validation messages for a model object data field
     *
     * @param String $field the model object data field to check
     * @return Array an array of validation messages for the data field
     */
    public function validation_messages_for($field)
    {
        return array_key($this->validation_messages(), $field, array());
    }

    /**
     * Return the first validation message for a model object data field
     *
     * @param String $field the model object data field to check
     * @return String the first validation message for the data field (or NULL)
     */
    public function validation_message_for($field)
    {
        $messages = $this->validation_messages_for($field);
        return (count($messages) ? $messages[0] : NULL);
    }

    /**
     * Return whether this model object is validated against validation methods
     *
     * @return Boolean whether this model validates against validation methods
     */
    public function is_validated()
    {
        $messages = array();
        $table = $this->get_table();
        foreach ($this->data() as $field => $value)
        {
            $rules = array_key(self::$validators, "$table.$field", array());
            foreach ($rules as $rule_and_args)
            {
                list($rule, $args) = $rule_and_args;
                $message = $args ? $this->$rule($value, $args)
                                 : $this->$rule($value);
                if ($message)
                {
                    if (NULL === array_key($messages, $field))
                    {
                        $messages[$field] = array();
                    }
                    $messages[$field][] = $message;
                }
            }
        }
        $this->validation_messages = $messages;
        return !$messages;
    }

    /**
     * Return whether a data field appears to be a valid person name
     *
     * @param String $name the data field value (a person name)
     * @return Boolean whether the data field appears to be a valid person name
     */
    protected function is_valid_person_name($name)
    {
        if (preg_match('/^[\w\s,\.\'\-]+$/', $name)) return NULL;
        return 'VALID_NAME_CAN_ONLY_INCLUDE_WORDS';
    }

    /**
     * Return whether a data field looks like a valid email address
     *
     * @param String $email the data field value (an email address)
     * @return Boolean whether the data field looks like a valid email address
     */
    protected function is_valid_email($email)
    {
        $at = strpos($email, '@');
        if ($at === FALSE) return 'VALID_EMAIL_NEEDS_AN_AT';
        if ($at === 0) return 'VALID_EMAIL_CANNOT_START_WITH_AT';
        if ($at === strlen($email)-1) return 'VALID_EMAIL_CANNOT_END_WITH_AT';
        $dot = strpos($email, '.');
        if ($dot === FALSE) return 'VALID_EMAIL_NEEDS_A_DOT';
        if ($dot === 0) return 'VALID_EMAIL_CANNOT_START_WITH_DOT';
        if ($dot === strlen($email)-1) return 'VALID_EMAIL_CANNOT_END_WITH_DOT';
    }

    /**
     * Return whether a data field is a valid length by applying length args
     *
     * @param String $value the data field value to check
     * @param Array $args an assoc array with keys "shortest" and/or "longest"
     * @return Boolean whether the data field is a valid length
     */
    protected function is_valid_length($value, $args)
    {
        $shortest = array_key($args, 'shortest');
        $longest = array_key($args, 'longest');
        if ($shortest && strlen($value) < $shortest) return 'VALID_LENGTH_TOO_SHORT';
        if ($longest && strlen($value) > $longest) return 'VALID_LENGTH_TOO_LONG';
    }

    /**
     * Return whether a password value is valid by comparing it to confirm
     *
     * @param String $value the value of the password that was entered
     * @param String $password_confirm_field the name of the confirmation field
     * @return Boolean whether the password value matches the confirmation field
     */
    protected function is_valid_password($value, $password_confirm_field)
    {
        if ($value != $this->$password_confirm_field) return 'VALID_PASSWORDS_DO_NOT_MATCH';
    }
}

// End of Valid_model.php
