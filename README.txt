Welcome to YAWF - Yet Another Web Framework
-------------------------------------------

Here's a quick 1, 2, 3 to get you started:
1) Set your "YAWF_ROOT" environment variable to this directory
2) Run "./admin/yawf create foo.org" to create a new web site
3) Configure your Apache to read vhost files from dir "vhosts"
4) Restart your Apache(?) web server with a command like this:
   > sudo apachectl restart
or > sudo apache2ctl restart

In the YAWF directory (i.e. the $YAWF_ROOT directory) you'll see:
* admin: This directory holds useful commands like "yawf"
* app: This folder holds the YAWF application, not the framework
* apps: This directory holds all *your* web applications
* logs: This directory *may* hold Apache log files for your apps
* vhosts: This directory holds the virtual host files for Apache
* yawf: This directory holds the YAWF framework, used by apps

YAWF is released under the terms of the GNU Public Licence 3.
If you've got any questions about YAWF, please email me here:
Kevin Hutchinson <kevin@guanoo.com>
