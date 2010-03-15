Welcome to YAWF - Yet Another Web Framework
-------------------------------------------

Here's a quick 1, 2, 3 to get you started:
1) Set your "YAWF_ROOT" environment variable to this directory
2) Run "./admin/yawf.cmd create foo.org" to create a web site
3) Restart your Apache(?) web server with a command like this:
   > sudo apachectl restart
   > sudo apache2ctl restart

You'll also need to edit your "/etc/hosts" file if you're running
YAWF on your laptop, and you'll need to add a line to your Apache
"httpd.conf" or "apache.conf" file - see step 2 for instructions.

In the YAWF directory (i.e. the $YAWF_ROOT directory) you'll see:
* apps: This directory holds all your web applications
* logs: This directory holds Apache logs files for your apps
* scripts: This directory holds useful scripts like "yawf"
* vhosts: This directory holds virtual host files for Apache
* yawf: This directory holds the YAWF framework, used by apps

YAWF is released under the terms of the GNU Public Licence 3.
If you've got any questions about YAWF, please email me here:
Kevin Hutchinson <kevin.hutchinson@guanoo.com>
