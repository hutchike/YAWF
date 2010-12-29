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

load_helper('Text');

/**
 * The Relating_model adds relationships to models via methods
 * "belongs_to", "has_a" and "has_many". Unlike other "sexier"
 * frameworks, this class doesn't support many-to-many relations.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Relating_model extends SQL_model implements Modelled, Persisted, Validated
{
    const BELONGS_TO = 'belongs_to';
    const HAS_A = 'has_a';
    const HAS_MANY = 'has_many';

    private static $relations = array();
    private static $aliases = array();

    /**
     * Setup a relationship whereby this model belongs to some other models
     *
     * @param Array a list of models that this model belongs to
     */
    protected function belongs_to()
    {
        $models = func_get_args();
        $options = array('is_singular' => TRUE, 'relation' => self::BELONGS_TO);
        $this->set_relations_for($models, $options);
    }

    /**
     * Setup a relationship whereby this model has one of some other models
     *
     * @param Array a list of models that this model has one of
     */
    protected function has_a()
    {
        $models = func_get_args();
        $options = array('is_singular' => TRUE, 'relation' => self::HAS_A);
        $this->set_relations_for($models, $options);
    }

    /**
     * Setup a relationship whereby this model has many of some other models
     *
     * @param Array a list of models that this model has many of
     */
    protected function has_many()
    {
        $models = func_get_args();
        $options = array('is_singular' => FALSE, 'relation' => self::HAS_MANY);
        $this->set_relations_for($models, $options);
    }

    /**
     * Override the standard "__get" method to support model relationships
     *
     * @param String $field_or_model an object field, or a related model to find
     * @return Object the field value, or a related model if a relation exists
     */
    public function __get($field_or_model)
    {
        $relation = $this->get_relation($field_or_model);
        if (is_null($relation)) return parent::__get($field_or_model);

        switch ($relation)
        {
            case self::BELONGS_TO:
                $model_id = $field_or_model . '_id';
                return $this->find_one_related($field_or_model, 'id = ' . $this->$model_id);

            case self::HAS_A:
                $id_field = $this->get_related_id_field();
                return $this->find_one_related($field_or_model, "$id_field = " . $this->get_id());

            case self::HAS_MANY:
                $id_field = $this->get_related_id_field();
                return $this->find_all_related($field_or_model, "$id_field = " . $this->get_id());
                break;

            default:
                throw new Exception("Unknown relation $relation");
        }
    }

    /**
     * Find one related model object given an SQL "where" clause
     *
     * @param String $model the name of the model
     * @param String $clause the SQL "where" clause
     * @return SQL_model the related model object that was found, or NULL
     */
    private function find_one_related($model, $clause)
    {
        $model = array_key(self::$aliases, $model, $model);
        $object = self::new_model_object_for($model);
        $found = $object->set_limit(1)->find_where($clause);
        return is_array($found) ? $found[0] : NULL;
    }

    /**
     * Find all related model objects given an SQL "where" clause
     *
     * @param String $model the name of the model
     * @param String $clause the SQL "where" clause
     * @return Array the related model objects that were found, or NULL
     */
    private function find_all_related($model, $clause)
    {
        $model = array_key(self::$aliases, $model, $model);
        $object = self::new_model_object_for($model);
        $found = $object->find_where($clause);
        return is_array($found) ? $found : NULL;
    }

    /**
     * Return a new model object, given a model name like "user_config"
     *
     * @param String $model the model name, normally with underscores
     * @return SQL_model a new model object
     */
    private static function new_model_object_for($model)
    {
        $class = Text::classify($model);
        return new $class();
    }

    /**
     * Setup a relationship whereby this model has a relation with other models.
     * As a side-effect the models will all be loaded and "setup" ready for use.
     *
     * @param Array $models a list of models that this model has a relation with
     * @param Array $options an array of options, including the relation type
     */
    private function set_relations_for($models, $options)
    {
        $is_singular = array_key($options, 'is_singular', FALSE);
        $relation = array_key($options, 'relation');
        foreach ($models as $model)
        {
            $options = array('is_singular' => $is_singular);
            if (is_array($model))
            {
                $options['alias'] = array_key($model, 'alias');
                $model = $model['model'];
            }
            $this->set_relation($model, $relation, $options);
            load_model($model);
        }
    }

    /**
     * Setup a relationship with another model, as singular or plural (default)
     *
     * @param String $model the name of the other model
     * @param String $relation the relation type, e.g. "belongs_to"
     * @param Array $options relationship options (e.g. "alias", "is_singular")
     */
    private function set_relation($model, $relation, $options = array())
    {
        $is_singular = array_key($options, 'is_singular', FALSE);
        $other_table = $is_singular ? Text::underscore($model)
                                    : Text::tableize($model);
        $table = $this->get_table();
        if ($alias = array_key($options, 'alias'))
        {
            self::$aliases[$alias] = $other_table;
            $other_table .= '.' . $alias;
        }
        self::$relations["$table.$other_table"] = $relation;
    }

    /**
     * Get any relationship with another model
     *
     * @param String $other_table the name of the other table (can be singular)
     * @return String any defined relation with the other table
     */
    private function get_relation($other_table)
    {
        $table = $this->get_table();
        if ($real_table = array_key(self::$aliases, $other_table))
        {
            $other_table = "$real_table.$other_table";
        }
        return array_key(self::$relations, "$table.$other_table");
    }

    /**
     * Get the name of this table's ID field on the related table e.g. "user_id"
     *
     * @return String the name of this table's ID field on the related table
     */
    private function get_related_id_field()
    {
        return Text::singularize($this->get_table()) . '_id';
    }
}

// End of Relating_model.php
