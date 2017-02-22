$(function(){
	if( $('#yummy-search-form').length > 0){
		
		$('#yummy-search-form select').change(function(){
			$(this).next('input[name="search"]').focus();
		});

		$('#yummy-search-form select').change(function(){
			$(this).next('input[name="search"]').focus();
		});

		$('#yummy-search-form button.minus-button').click(function(){
			$(this).closest('.yummy-search-row').remove();
		});

		$('#yummy-search-form button.plus-button').click(function(){
			var row = $('.yummy-search-row').first().clone();
			
			$(row).find('input[name="YummySearch[field][]"] option:eq(0)').prop('selected', true);
			$(row).find('input[name="YummySearch[operator][]"] option:eq(0)').prop('selected', true);
			$(row).find('input[name="YummySearch[search][]"]').val('');

			$(row).find('button.plus-button').hide();

			$(row).find('button.minus-button').show().click(function(){
				$(this).closest('.yummy-search-row').remove();
			});

			$(row).insertAfter($('.yummy-search-row').last());
		});
	}
});
/**
 * I looked at rewriting this into generic JS for mass-appeal, but then realized just how easy jQuery makes things. If you rewrite I'll accept into master
document.addEventListener('DOMContentLoaded', function() {
	
	var YummySearch = document.getElementById('yummy-search-form');
	console.log(YummySearch);
	if( YummySearch.length > 0 ){
		
		YummySearch.addEventListener('click', function(e){
			e = e || window.event;
			var target = e.target || e.srcElement;
			var type
			console.log(target.type)
			console.log(target.className)
			
			if( target.type == 'select' ){
				
			}

			if( target.type == 'button' && target.className.match('minus-button') ){
				
			}
			
			//var type = target.type;
			//var class = target.class;
			
		},false);
	}
});

 */