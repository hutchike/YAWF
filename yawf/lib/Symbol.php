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

// PHP doesn't have symbols like Ruby, but we can use constants instead

class Symbol extends YAWF
{
    const APP = 'app';
    const CONTROLLER = 'controller';
    const COOKIE = 'cookie';
    const DEFAULT_WORD = 'default';
    const FLASH = 'flash';
    const HTML = 'html';
    const JS = 'js';
    const JSON = 'json';
    const JSONP = 'jsonp';
    const MAILER = 'mailer';
    const PATH_CONFIG = 'path_config';
    const PATH_METHOD = 'path_method';
    const SERIALIZED = 'serialized';
    const SERVER = 'server';
    const SERVICE = 'service';
    const SESSION = 'session';
    const TXT = 'txt';
    const VALIDATION_MESSAGES = 'validation_messages';
    const XML = 'xml';
    const YAML = 'yaml';
}

// End of Symbol.php
