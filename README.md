WordPress AntiClone
====================

A WordPress plugin to reduce the impact of unauthorized clones of your blog.

# What is this plugin for?
Currenty there is a wave of cloned blogs going around. Popular blogs are being cloned on foreign domains, mostly with another top level domain. The "cloner" than tries to maximize traffic on that illegally cloned page and generate revenue from injected advertisements.

Most of the time the original website will be simply "proxied" by the attacker but automatically filters out all script tags. However it is still possible to execute JS code from event handlers which is exactly what this plugin does. For more information about this, visit these  (German) websites:
 * http://niedblog.de/blog-kopiert-name-geklaut/
 * http://sectio-aurea.org/2014/08/blog-kopiert-technische-gegenmassnahmen/

# A Word of Warning
This plugin is experimental! It should work and has been tested on a blog using minification, lazy image loading etc. Howerver it is not guaranteed, that it will work for you as well. Also please note that this plugin **DOES NOT** avoid your blog being cloned but will reduce the impact of a cloned blog by redirecting its visitors to your original blog and replacing the cloned content with a warning message!

# Setup
Go to your WordPress settings first and configure a list of authorized domains for your blog first. The list is comma-separated and could look like this: "passiondriving.de,passion-driving.de"

You don't need to list your domain with www. The plugin does detect this automatically. If your blog is hosted on a subdomain, you should add this as well.

# How it works
Basically this plugin adds an image to the footer of your website. Inside the onload-Event of that image JavaScript code will be executed to check whether the URL in the browser is one of the authorized URLs. If not the website content will be replaced by a warning message and the user will be redirected to the original URL on your blog.

# Help! It doesn't work!
If the plugin does not work for you, just let me know and open an issue here on GitHub!
