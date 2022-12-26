$(function()
{
	var $window = $(window);
	
	var $content = $('#content'), $center = $('#center');
	
	var header = $('#header').outerHeight(true);
	var footer = $('#footer').outerHeight(true);
	
	var height = $center.outerHeight(true);
	
	$window.resize(function()
	{
		var window = $window.height();
		var offset = window - header - footer - 54;
		
		$content.css('height', offset);
		
		if(offset < height)
		{
			$center.css('top', 0);
		} else
		{
			$center.css('top', (offset - height)/2);
		}
	});
	
	$window.resize();
});