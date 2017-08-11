(function($){
	$('.dgffg_more_albums').bind('click', function(e){
		e.preventDefault();
		var
			url = digigroup_fb_fancy_gallery.ajaxurl
			action = ''
			next = $(this).attr('href')
			width_thumbs = $("#dgffg_width_thumbs").val()
			html = ""
			album_item_tag = $("#dgffg_album_item_tag").val()
		;
		$(this).hide();
		$('.dgffg_loading').show();
		$.ajax({

	        type: 'GET',
	        url: url,
	        dataType: "json",
	        data: {
	            action: 'more_albums',
	            next: next,
	            width_thumbs: width_thumbs

	        },
	        // loading
	        success:function(data){
	        	$.each(data.data, function(i, item){
	            	if( item.count > 0 && item.type === "normal") {
		            	html += '<' +album_item_tag+ '>';
		            	html += '<div class="dgffg_picture_container">'; // Controlar shortcode...
		            	html += '<img src="'+item.cover_photo+'"/>';
		            	html += '</div>'; // Controlar shortcode...
		            	html += '<a href="https://graph.facebook.com/'+item.id+'/photos" class="dgffg_album_link">'+item.name+'</a>';
		            	html += '</' +album_item_tag+ '>';
	            	}
	            });
	            
	            $('#dgffg_gallery_container_items').append(html);
	            // Se next não existir esconder o botão mais albums
	            $('.dgffg_more_albums').attr('href', data.paging.next);

				$('.dgffg_loading').hide();
				
				if( data.paging.next === undefined )
	            	$('.dgffg_more_albums').hide();
	            else
					$('.dgffg_more_albums').show();

	        },
	        error: function(MLHttpRequest, textStatus, errorThrown){
	            console.log(errorThrown);
	            $('.dgffg_loading').hide();
				$('.dgffg_more_albums').show();

	        }
        });
	});

	$('.dgffg_album_link').live('click', function(e){
		e.preventDefault();

		var 
			html = ""
			url = $(this).attr('href') + '&callback=?'
			album_name = $(this).text()
			legend_photo = ""
			width_thumbs = $("#dgffg_width_thumbs").val()
		;

		$.getJSON(url, function(data) {
			
			html += '<h1>'+album_name+'</h1>';
			html += '<ul>';
			
			$.each(data.data,function(i, item){
				
				if( item.name !== undefined )
					legend_photo = item.name;
				//html += '<li><a class="fancybox-thumbs" rel="fancybox-thumb" title="'+legend_photo+'" href="'+item.source+'"><img src="'+item.picture+'" alt="" /></a></li>'; 
				html += '<li><div class="dgffg_picture_container"><a class="fancybox-thumbs" rel="fancybox-thumb" title="'+legend_photo+'" href="'+item.source+'"><img src="'+item.images[width_thumbs].source+'" alt="" /></a></div></li>'; 
			});

			html += '</ul>';			

			html += '<a href="javascript:void(0);" id="dgffg_return_to_albums">Voltar</a>';

			$("#dgffg_details_album").html(html);
		});

		$("#dgffg_main_container").hide();
		$("#dgffg_details_album").show();

	});
	$('a#dgffg_return_to_albums').live('click', function(){
		$("#dgffg_details_album").html('');
		$("#dgffg_details_album").hide();
		$("#dgffg_main_container").show();

	});

	$('.fancybox-thumbs').fancybox({
		prevEffect : 'none',
		nextEffect : 'none',

		closeBtn  : true,
		arrows    : false,
		nextClick : true,

		helpers : {
			overlay : {
            	showEarly : false,
            	//closeClick: false
        	},
			//overlay : null,
			thumbs : {
				width  : 50,
				height : 50
			}
		}
	});

})(jQuery);