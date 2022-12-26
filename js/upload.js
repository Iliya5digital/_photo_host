var uploading = false, $body, $iframes = [], $list, $album_size, $empty, $uploader, $info, $cover, files = {}, $initiate, $cancel, amount = 0, api, disable_upload = 0, $computer, $web;

// Output error
function trigger_error(key)
{
	$info.removeClass('notification').addClass('error').html(error[key]);
	
	return false;
}

// Uploader
var Uploader =
{
	'start': function()
	{
		var path;
		
		uploading = true;
		
		$cancel.show();
		$initiate.hide();
		
		$computer.removeClass('enabled');
		$web.removeClass('enabled');
		
		$info.html('').attr('class', 'progress');
		
		this.$progress_bar = $('<span class="bar"></span>').appendTo($info);
		this.$progress_text = $('<span class="text">0%</span>').appendTo($info);
		
		this.list = [];
		
		this.sent = 0;
		this.total = 0;
		this.index = 0;
		
		for(path in files)
		{
			this.list.push(files[path]);
			
			this.total += files[path].size;
		}
		
		this.list.reverse();
		
		this.xhr = $.ajax(
		{
			'url': WEB+'php/upload_start.php?id='+UPLOAD_ID,
			'type': 'POST',
			'dataType': 'html',
			'success': function(response)
			{
				Uploader.album_id = response;
				
				Uploader.uploading = true;
				
				Uploader.next();
			}
		});
	},
	
	'cancel': function()
	{
		var path;
		
		this.xhr.abort();
		
		if(this.index)
		{
			this.list[this.index-1].abort();
		}
		
		Uploader.uploading = false;
		
		$cancel.hide();
		$initiate.show();
		
		$info.attr('class', 'notification').html(info);
		
		for(path in files)
		{
			$('img', files[path].$p).attr('src', WEB+'image/img.png');
		}
		
		$.ajax(
		{
			'url': WEB+'php/upload_cancel.php?id='+UPLOAD_ID,
			'type': 'POST',
			'dataType': 'html',
			'success': function()
			{
				uploading = false;
			}
		});
	},
	
	'progress': function(bytes)
	{
		this.sent += bytes;
		
		var pct = Math.round(this.sent/this.total*100)+'%';
		
		this.$progress_bar.css('width', pct);
		this.$progress_text.html(pct);
	},
	
	'next': function()
	{
		if(!this.uploading) return;
		
		if(this.index)
		{
			$('img', this.list[this.index-1].$p).attr('src', WEB+'image/uploaded.png');
		}
		
		if(this.index < this.list.length)
		{
			var file = this.list[this.index++];
			
			$('img', file.$p).attr('src', WEB+'image/loading.gif');
			
			file.upload();
		} else
		{
			document.location = WEB+'a/'+Uploader.album_id;
		}
	}
};

// File Handler
var FileHandler =
{
	'types': ['image/jpeg', 'image/png', 'image/gif', 'image/wbmp'],
	
	'valid': function(file)
	{
		if(file.path in files) return false;
		
		if(amount === MAX_ALBUM_SIZE)
		{
			return trigger_error(3);
		}
		
		return true;
	},
	
	'add': function(file)
	{
		file.$p = $('<p><img src="'+WEB+'image/img.png" alt="Image"/><span class="name">'+file.name+'</span></p>').append('<div style="clear: both;"/>').prependTo($list).mousedown(function(e)
		{
			e.preventDefault();
		}).click(function()
		{
			if(!uploading) FileHandler.remove(file);
		}).mouseenter(function()
		{
			if(!uploading) file.$p.addClass('hover');
		}).mouseleave(function()
		{
			file.$p.removeClass('hover');
		});
		
		files[file.path] = file;
		
		if(!amount++)
		{
			$empty.hide();
			
			$initiate.addClass('enabled');
		}
		
		$album_size.html(amount);
	},
	
	'remove': function(file)
	{
		file.$p.remove();
		
		$album_size.html(--amount);
		
		if(!amount)
		{
			$empty.show();
			
			$initiate.removeClass('enabled');
		}
		
		file.remove();
		
		delete files[file.path];
	}
};

// Real file
function File(handle)
{
	this.name = this.path = handle.name;
	
	if(!FileHandler.valid(this)) return;
	
	// Validation
	if(handle.size > MAX_FILE_SIZE) return trigger_error(2);
	if(FileHandler.types.indexOf(handle.type) === -1) return trigger_error(1);
	
	// Remove
	this.remove = function()
	{
		$iframes[0][0].contentWindow.document.location = 'javascript: document.getElementById("input").value = "";';
	};
	
	// Upload
	var xhr;
	
	this.upload = function()
	{
		xhr = new XMLHttpRequest(), progress = 0;
		
		xhr.onreadystatechange = function(e)
		{
			if(xhr.readyState === 4)
			{
				Uploader.next();
			}
		};
		
		xhr.upload.addEventListener('progress', function(e)
		{
			Uploader.progress(e.loaded - progress);
			
			progress = e.loaded;
		});
		
		xhr.open('POST', WEB+'php/upload.php', true);
		xhr.setRequestHeader('ID', UPLOAD_ID);
		xhr.send(handle);
	};
	
	this.abort = function()
	{
		xhr.abort();
	};
	
	// Size
	this.size = handle.size;
	
	// Add
	FileHandler.add(this);
}

// Fake file
function FakeFile(handle)
{
	this.path = handle.value;
	
	if(!FileHandler.valid(this)) return;
	
	var self = this, data;
	
	if(handle.value.substr(0, 1) === '/')
	{
		data = handle.value.split('/');
	} else
	{
		data = handle.value.split('\\');
	}
	
	this.name = data[data.length - 1];
	this.$iframe = $iframes[$iframes.length-1];
	
	// Remove
	this.remove = function()
	{
		this.$iframe.remove();
	};
	
	// Upload
	this.upload = function()
	{
		$uploader.unbind('load').load(function()
		{
			Uploader.progress(self.size);
			
			Uploader.next();
		});
		
		this.$iframe[0].contentWindow.document.location = 'javascript: document.getElementById("form").submit();';
	};
	
	this.abort = function()
	{
		
	};
	
	// Validation
	$uploader.unbind('load').load(function()
	{
		var result = this.contentWindow.document.body.innerHTML;
		
		if(result.length === 1)
		{
			trigger_error(parseInt(result));
		} else
		{
			self.size = parseInt(result.substr(1));
			
			FileHandler.add(self);
		}
	});
	
	this.$iframe[0].contentWindow.document.location = 'javascript: document.getElementById("form").submit();';
	
	ReCreate();
}

// Web File
function WebFile(url)
{
	var self = this;
	
	if(url === '' || url === 'http://') return;
	
	this.path = url;
	
	if(this.path.substr(0, 7) !== 'http://' && this.path.substr(0, 8) !== 'https://' && this.path.substr(0, 6) !== 'ftp://') this.path = 'http://'+this.path;
	
	if(!FileHandler.valid(this)) return;
	
	var data = url.split('/');
	
	this.name = data[data.length - ((data[data.length-1] === '')?2:1)];
	
	disable_upload++;
	
	FileHandler.add(self);
	
	// IMG
	var $img = $('img', this.$p);
	
	$img.attr('src', WEB+'image/loading.gif');
	
	// Upload
	var xhr;
	
	this.upload = function()
	{
		xhr = $.ajax(
		{
			'url': WEB+'php/upload.php',
			'type': 'POST',
			'data': {'id': UPLOAD_ID, 'url': this.path},
			'success': function()
			{
				Uploader.progress(self.size);
				
				Uploader.next();
			}
		});
	};
	
	this.abort = function()
	{
		xhr.abort();
	};
	
	// Remove
	this.disabling = true;
	
	this.remove = function()
	{
		xhr.abort();
		
		if(this.disabling) disable_upload--;
	};
	
	// Validation
	xhr = $.ajax(
	{
		'url': WEB+'php/upload.php',
		'type': 'POST',
		'data': {'id': UPLOAD_ID, 'url': this.path},
		'dataType': 'html',
		'success': function(result)
		{
			if(result.length === 1)
			{
				FileHandler.remove(self);
				
				trigger_error(parseInt(result));
			} else
			{
				self.size = parseInt(result.substr(1));
				
				$img.attr('src', WEB+'image/img.png');
			}
			
			disable_upload--;
			
			self.disabling = false;
		}
	});
}

// ReCreate
function ReCreate()
{
	var form;
	
	if(api)
	{
		form = '<form id="form"><input id="input" name="images[]" type="file" multiple/></form>';
	} else
	{
		form = '<form id="form" method="POST" action="'+WEB+'php/upload.php?id='+UPLOAD_ID+'" enctype="multipart/form-data" target="upload_iframe"><input type="hidden" name="MAX_FILE_SIZE" value="'+MAX_FILE_SIZE+'"/><input id="input" name="image" type="file"/></form>';
	}
	
	$iframes.push($('<iframe style="display: none;"/>').load(function()
	{
		this.contentWindow.document.body.innerHTML = form;
		
		$(this.contentWindow.document.getElementById('input')).change(function()
		{
			var i;
			
			if(api)
			{
				for(i=0; i<this.files.length; i++)
				{
					new File(this.files[i]);
				}
			} else
			{
				new FakeFile(this);
			}
		});
	}).appendTo($body));
}

// Initiate
$(function()
{
	api = typeof(document.createElement('input').files) === 'object';
	
	$body = $('body');
	$info = $('#upload_info');
	$list = $('#upload_list');
	$cover = $('<div id="drop"/>');
	$empty = $('#upload_list_empty');
	$album_size = $('#album_size');
	
	$uploader = $('<iframe style="display: none;" name="upload_iframe"/>').appendTo($body);
	
	ReCreate();
	
	// Browsing
	$computer = $('#upload_computer').click(function()
	{
		if(!uploading) $iframes[$iframes.length-1][0].contentWindow.document.location = 'javascript: document.getElementById("input").click();';
	});
	
	$web = $('#upload_web').click(function()
	{
		if(uploading) return;
		
		var $input = $('<input type="text" value="http://"/>');
		var $form = $('<form><img src="'+WEB+'image/img.png"></form>').append($input).append('<div style="clear: both;"/>').prependTo($list);
		
		$empty.hide();
		
		function save(e)
		{
			var value = $input.val();
			
			e.preventDefault();
			
			$form.remove();
			
			new WebFile(value);
			
			if(!amount) $empty.show();
		}
		
		$form.submit(save);
		
		$input.select().blur(function()
		{
			$form.remove();
			
			if(!amount) $empty.show();
		});
	});
	
	// Dragging
	var leave = false;
	
	if(api)
	{
		function drop(e)
		{
			var i;
			
			e.preventDefault();
			
			for(i=0; i<e.dataTransfer.files.length; i++)
			{
				new File(e.dataTransfer.files[i]);
			}
		}
		
		function dragenter(e)
		{
			e.preventDefault();
			
			$cover.prependTo($body);
			
			window.removeEventListener('dragenter', dragenter);
			
			$cover[0].addEventListener('dragover', dragover);
			$cover[0].addEventListener('drop', drop);
		}
		
		function dragover(e)
		{
			e.preventDefault();
			
			if(leave !== false) clearTimeout(leave);
			
			leave = setTimeout(function()
			{
				$cover.remove();
				
				window.addEventListener('dragenter', dragenter);
			}, 200);
		}
		
		window.addEventListener('dragenter', dragenter);
	}
	
	// Initiate uploading	
	$initiate = $('#upload_initiate').click(function()
	{
		if(amount && !uploading && !disable_upload) Uploader.start();
	});
	
	// Cancel uploading
	$cancel = $('#upload_cancel').click(function()
	{
		Uploader.cancel();
	});
});