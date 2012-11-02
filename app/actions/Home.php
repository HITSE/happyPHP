<?php

class Home{
	function run(){
		F3::set('title',"HappyQueue");
		echo Template::serve('index.html');
	}

	function noaccess()
	{
		F3::reroute('/');
	}

};

?>
