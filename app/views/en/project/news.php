<h4>Jan 3, 2011: Added remote data services over REST</h4>
YAWF now supports remote data services over REST. This means you can wrap a
model object in a "Remote" object like this:
<?= highlight('
Remote::set_default("server", "http://some.website.com");
$u = new User(array("name" => "Mr Smith"));
$u = new Remote($u); // wrap in remote object
$id = $u->save(); // save to the remove server
print "id is $id"; // get the object\'s ID
') ?>

<h4>Nov 3, 2010: Added REST web services</h4>
YAWF now supports REST web services in XML, JSON (and even <a href="http://yaml.org/">YAML</a>).
