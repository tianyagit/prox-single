/*
 * 单选多选插件
 * 自己写的 待完善
 */
(function($){
	$.fn.we7_check = function(){
		var _this = $(this);
		function markChenck() {
			_this.find(':radio,:checkbox').each(function(){
				var $this = $(this);
				var $this_type = $this.attr('type');
				var $this_class  = $this.attr('class') ? ' ' +  $this.attr('class') :'';
				var $this_style = $this.attr('style') ? 'style="' + $this.attr('style') + '"' :'';
				var $this_option = $this.prev('.we7-option').length;
				var $this_label = $this.parent('label').html();
				var isCheck = $this.is(':checked') ? ' checked ' : '';
				var isDisabled =  $this.is(':disabled') ? ' disabled ' : '';
				if(!$this_option) {
					$this.before('<div class="we7-option we7-'+$this_type+'-option'+ isCheck + isDisabled + $this_class +'"' + $this_style + '></div>');
					$this.on('click',function(){
						checked();
					});
					$this.on('change',function(){
						checked();
					})
				}
				if(!$this_label) {
					$this.prev('.we7-option:not(".disable")').on('click',function(){
						$this.trigger("click");
						if($this_type == 'radio') {
							$this.trigger("click");
						} 
					});
				}
			});
		}
		function checked() {
			$(':radio,:checkbox').each(function(){
				if($(this).is(':checked')){
					$(this).prev('.we7-option').addClass('checked');
				} else{
					$(this).prev('.we7-option').removeClass('checked');
				}
			});
		}
		markChenck();
	}
 })(jQuery);