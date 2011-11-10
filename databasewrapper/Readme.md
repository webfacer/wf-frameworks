# [WF Databeswrapper](http://webfacer.com)

A small usage of connecting to MySQL Database and quick fetching data.


## Changelogs

* Create Wikipages Documentation How to use
* Add to GitHub Repository


## Todo

* need to be reworked for some reason i add so many variables i do not need
* Querycounter does not work
* data which inserted doesnt esacpe do not now why


## Quick start

`
<?php				
$wfdb = WF_DB::connect();
			
$query = $wfdb->select('face_content');
$test = $wfdb->select('face_content',array('where'=>array('ID='=>71),'ID','content_title'));
?>
`
## Features

* wrapper used with MySQL
* Errorreporting
* fetching, insert, update & delete
* statements : insert, update, delete, order, like, where, limit, select...


## Contributing

Anyone is welcome. Newcomer to PHP who are here to learn and to create something for fun and work. Creating Frameworks to reuse and more important to learn everday new things :) have fun


## License...

under Creative Commons see: http://creativecommons.org/licenses/by-nc-nd/3.0/