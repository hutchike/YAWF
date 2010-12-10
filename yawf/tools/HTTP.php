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
 * This helper provides commonly used HTTP status codes via class
 * constants such as HTTP::OK which just returns 200. Simple huh?
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class HTTP extends YAWF
{
    // 200 status codes
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    // Add more here...

    // 300 status codes

    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    // Add more here...

    // 400 status codes

    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    // Add more here...

    // 500 status codes

    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    // Add more here...
}

// End of HTTP.php
