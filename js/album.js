$(function()
{
	var $slide = $('#slide'), $left = $('#slide_left'), $right = $('#slide_right');
	
	$left.removeAttr('href');
	$right.removeAttr('href');
	
	$left.click(function(e)
	{
		e.preventDefault();
		
		if(SLIDE > 0)
		{
			$right.removeClass('disabled');
			
			if(--SLIDE === 0)
			{
				$left.addClass('disabled');
			}
			
			$slide.animate({'left': '+=600px'});
		}
	});
	
	$right.click(function(e)
	{
		e.preventDefault();
		
		if(SLIDE < MAX_SLIDE)
		{
			$left.removeClass('disabled');
			
			if(++SLIDE === MAX_SLIDE)
			{
				$right.addClass('disabled');
			}
			
			$slide.animate({'left': '-=600px'});
		}
	});
	
	$('#information input').click(function()
	{
		$(this).select();
	});
});