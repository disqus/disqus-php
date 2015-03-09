disqus-php
~~~~~~~~~~

Requires PHP 5.3.0 or newer!

Use the API by instantiating it, and then calling the method through dotted notation chaining::

	require('disqusapi/disqusapi.php');
	$disqus = new DisqusAPI($secret_key);
	$disqus->trends->listThreads();

Advanced Example	
~~~~~~~~~
* How to integrate Disqus API with your CMS 
	(http://codexc.com/blog/2015/03/disqus-comments-moderation-in-your-cms/)

Parameters (including the ability to override version, api_secret, and format) are passed as keyword arguments to the resource call::

	$disqus->posts->details(array('post'=>1, 'version'=>'3.0'));

Documentation on all methods, as well as general API usage can be found at http://disqus.com/api/
