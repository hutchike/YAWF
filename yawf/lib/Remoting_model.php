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

/**
 * The Remoting_model adds remote-ability to models via methods
 * "set_remote" to setup which models will be remote, and "make"
 * to create a new model object, which may or may not be remote.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Remoting_model extends Relating_model implements Modelled, Persisted, Validated
{
    private static $is_remoted = array();

    /**
     * Set a model as a remote model
     *
     * @param String/Array $model model name(s) to set as remote (or not)
     * @param Boolean $is_remote whether the model(s) are remote (default TRUE)
     */
    public static function set_remote($model, $is_remote = TRUE)
    {
        $models = is_array($model) ? $model : array($model);
        foreach ($models as $model)
        {
            self::$is_remoted[$model] = $is_remote;
        }
    }

    /**
     * Make a new model object, optionally with some initialization data
     *
     * @param String $model the name of the model class to make
     * @param Array $data an optional array of initialization data
     * @param Boolean $has_changed has the new object has changed (default TRUE)
     * @return Remote/SQL_model the newly made model object
     */
    public static function make($model, $data = array(), $has_changed = TRUE)
    {
        $object = new $model($data, $has_changed);
        if (array_key(self::$is_remoted, Symbol::ALL) ||
            array_key(self::$is_remoted, $model)) $object = new Remote($object);
        return $object;
    }
}

// End of Remoting_model.php
