(function(window) {
var util = {};
util.dialog = function(title, content, footer, options) {
	if(!options) {
		options = {};
	}
	if(!options.containerName) {
		options.containerName = 'modal-message';
	}
	var modalobj = $('#' + options.containerName);
	if(modalobj.length == 0) {
		$(document.body).append('<div id="' + options.containerName + '" class="modal animated" tabindex="-1" role="dialog" aria-hidden="true"></div>');
		modalobj = $('#' + options.containerName);
	}
	var html =
		'<div class="modal-dialog modal-sm">'+
		'	<div class="modal-content">';
	if(title) {
		html +=
		'<div class="modal-header">'+
		'	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
		'	<h3>' + title + '</h3>'+
		'</div>';
	}
	if(content) {
		if(!$.isArray(content)) {
			html += '<div class="modal-body">'+ content + '</div>';
		} else {
			html += '<div class="modal-body">正在加载中</div>';
		}
	}
	if(footer) {
		html +=
		'<div class="modal-footer">'+ footer + '</div>';
	}
	html += '	</div></div>';
	modalobj.html(html);
	if(content && $.isArray(content)) {
		var embed = function(c) {
			modalobj.find('.modal-body').html(c);
		};
		if(content.length == 2) {
			$.post(content[0], content[1]).success(embed);
		} else {
			$.get(content[0]).success(embed);
		}
	}
	return modalobj;
};
util.image = function(obj, callback, options) {
	require(['webuploader', 'cropper', 'previewer'], function(WebUploader){
		var i = util.querystring('i'),j = util.querystring('j'), cropperPanel, uploader, previewer;
		defaultOptions = {
			pick: {
				id: '#filePicker',
				label: '点击选择图片',
				multiple : false
			},
			auto: true,
			swf: './resource/componets/webuploader/Uploader.swf',
			server: './index.php?i='+i+'&j='+j+'&c=utility&a=file&do=upload&type=image&thumb=0',
			chunked: false,
			compress: false,
			fileNumLimit: 1,
			fileSizeLimit: 4 * 1024 * 1024,
			fileSingleSizeLimit: 4 * 1024 * 1024,
			crop : false,
			preview : false
		};
		if (util.agent() == 'android') {
			defaultOptions.sendAsBinary = true;
		}
		options = $.extend({}, defaultOptions, options);
		if (obj) {
			obj = $(obj);
			options.pick = {id : obj, multiple : options.pick.multiple};
		}
		if (options.multiple) {
			options.pick.multiple = options.multiple;
			options.fileNumLimit = 8;
		}
		if (options.crop) {
			options.auto = false;
			options.pick.multiple = false;
			options.preview = false;
			WebUploader.Uploader.register({
				'before-send-file': 'cropImage'
			}, {
				cropImage: function(file) {
					if (!file || !file._cropData) {
						return false;
					}
					var data = file._cropData, image, deferred;
					file = this.request('get-file', file);
					deferred = WebUploader.Deferred();
					image = new WebUploader.Lib.Image();
					deferred.always(function() {
						image.destroy();
						image = null;
					});
					image.once( 'error', deferred.reject );
					image.once( 'load', function() {
						image.crop(data.x, data.y, data.width, data.height, data.scale);
					});
					image.once( 'complete', function() {
						var blob, size;
						// 移动端 UC / qq 浏览器的无图模式下
						// ctx.getImageData 处理大图的时候会报 Exception
						// INDEX_SIZE_ERR: DOM Exception 1
						try {
							blob = image.getAsBlob();
							size = file.size;
							file.source = blob;
							file.size = blob.size;
							file.trigger('resize', blob.size, size);
							deferred.resolve();
						} catch (e) {
							// 出错了直接继续，让其上传原始图片
							deferred.resolve();
						}
					});
					file._info && image.info(file._info);
					file._meta && image.meta(file._meta);
					image.loadFromBlob(file.source);
					return deferred.promise();
				}
			});
		}
		
		uploader = WebUploader.create(options);
		obj.data('uploader', uploader);
		//开启预览
		if (options.preview) {
			previewer = mui.previewImage({footer : window.util.templates['image.preview.html']});
			$(previewer.element).find('.js-cancel').click(function(){
				previewer.close();
			});
			$(document).on('click', '.js-submit', function(event) {
				var index = $(previewer.element).find('.mui-slider-group .mui-active').index();
				if (previewer.groups['__IMG_UPLOAD'] && previewer.groups['__IMG_UPLOAD'][index] && previewer.groups['__IMG_UPLOAD'][index]['el']) {
					var url = './index.php?i='+i+'&j='+j+'&c=utility&a=file&do=delete&type=image';
					var id = $(previewer.groups['__IMG_UPLOAD'][index]['el']).data('id');
					$.post(url, {id :id}, function(data) {
						var data = $.parseJSON(data);
						$(previewer.groups['__IMG_UPLOAD'][index]['el']).remove();
						previewer.close();
					});
				}
				event.stopPropagation();
				return false;
			});
		}
		uploader.on('fileQueued', function(file) {
			util.loading().show();
			if (options.crop) {
				uploader.makeThumb(file, function(error, source) {
					uploader.file = file;
					if (error) {
						
					} else {
						cropperPanel.preview(source);
					}
				}, 1, 1);
			}
		});
		uploader.on('uploadSuccess', function(file, result) {
			if(result.error && result.error.message){
				util.toast(result.error.message, 'error');
			} else {
				uploader.on("uploadFinished", function() {
					util.loading().close();
					uploader.reset();
					cropperPanel.reset();
				});
				var img = $('<img src="'+result['url']+'" data-preview-src="" data-preview-group="'+options.preview+'" />');
				if (options.preview) {
					previewer.addImage(img[0]);
				}
				if($.isFunction(callback)){
					callback(result);
				}
			}
		});
		uploader.onError = function( code ) {
			cropperPanel.reset();
			util.loading().close();
			if(code == 'Q_EXCEED_SIZE_LIMIT'){
				alert('错误信息: 图片大于 4M 无法上传.');
				return;
			} else if (code == 'Q_EXCEED_NUM_LIMIT') {
				util.toast('单次最多上传8张')
				return;
			}
			alert('错误信息: ' + code );
		};
		cropperPanel = (function() {
			var avatarPreview, image;
			return {
				preview: function(source) {
					avatarPreview = $(window.util.templates['avatar.preview.html']);
					avatarPreview.css('height', $(window).height());
					$(document.body).prepend(avatarPreview);
					image = avatarPreview.find('img');
					image.attr('src', source);
					image.cropper({
						aspectRatio: options.aspectRatio ? options.aspectRatio : 1 / 1,
						viewMode: 1,
						dragMode: 'move',
						autoCropArea: 1,
						restore: false,
						guides: false,
						highlight: false,
						cropBoxMovable: false,
						cropBoxResizable: false,
					});
					avatarPreview.find('.js-submit').on('click', function(){
						var data = image.cropper('getData');
						var scale = cropperPanel.getImageSize().width / uploader.file._info.width;
						data.scale = scale;
						uploader.file._cropData = {
							x: data.x,
							y: data.y,
							width: data.width,
							height: data.height,
							scale: data.scale
						};
						uploader.upload();
					});
					avatarPreview.find('.js-cancel').one('click', function(){
						avatarPreview.remove();
						uploader.reset();
					});
					util.loading().close();
					return this;
				},
				getImageSize: function() {
					var img = image.get(0);
					return {
						width: img.naturalWidth,
						height: img.naturalHeight
					}
				},
				reset : function() {
					$('.js-avatar-preview').remove();
					uploader.reset();
					return this;
				}
			}
		})();
	});
};
util.map = function(val, callback){
	require(['map'], function(BMap){
		if(!val) {
			val = {};
		}
		if(!val.lng) {
			val.lng = 116.403851;
		}
		if(!val.lat) {
			val.lat = 39.915177;
		}
		var point = new BMap.Point(val.lng, val.lat);
		var geo = new BMap.Geocoder();
		
		var modalobj = $('#map-dialog');
		if(modalobj.length == 0) {
			var content =
				'<div class="form-group">' +
					'<div class="input-group">' +
						'<input type="text" class="form-control" placeholder="请输入地址来直接查找相关位置">' +
						'<div class="input-group-btn">' +
							'<button class="btn btn-default"><i class="icon-search"></i> 搜索</button>' +
						'</div>' +
					'</div>' +
				'</div>' +
				'<div id="map-container" style="height:400px;"></div>';
			var footer =
				'<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
				'<button type="button" class="btn btn-primary">确认</button>';
			modalobj = util.dialog('请选择地点', content, footer, {containerName : 'map-dialog'});
			modalobj.find('.modal-dialog').css('width', '80%');
			modalobj.modal({'keyboard': false});
			
			map = util.map.instance = new BMap.Map('map-container');
			map.centerAndZoom(point, 12);
			map.enableScrollWheelZoom();
			map.enableDragging();
			map.enableContinuousZoom();
			map.addControl(new BMap.NavigationControl());
			map.addControl(new BMap.OverviewMapControl());
			marker = util.map.marker = new BMap.Marker(point);
			marker.setLabel(new BMap.Label('请您移动此标记，选择您的坐标！', {'offset': new BMap.Size(10,-20)}));
			map.addOverlay(marker);
			marker.enableDragging();
			marker.addEventListener('dragend', function(e){
				var point = marker.getPosition();
				geo.getLocation(point, function(address){
					modalobj.find('.input-group :text').val(address.address);
				});
			});
			function searchAddress(address) {
				geo.getPoint(address, function(point){
					map.panTo(point);
					marker.setPosition(point);
					marker.setAnimation(BMAP_ANIMATION_BOUNCE);
					setTimeout(function(){marker.setAnimation(null)}, 3600);
				});
			}
			modalobj.find('.input-group :text').keydown(function(e){
				if(e.keyCode == 13) {
					var kw = $(this).val();
					searchAddress(kw);
				}
			});
			modalobj.find('.input-group button').click(function(){
				var kw = $(this).parent().prev().val();
				searchAddress(kw);
			});
		}
		modalobj.off('shown.bs.modal');
		modalobj.on('shown.bs.modal', function(){
			marker.setPosition(point);
			map.panTo(marker.getPosition());
		});
		
		modalobj.find('button.btn-primary').off('click');
		modalobj.find('button.btn-primary').on('click', function(){
			if($.isFunction(callback)) {
				var point = util.map.marker.getPosition();
				geo.getLocation(point, function(address){
					var val = {lng: point.lng, lat: point.lat, label: address.address};
					callback(val);
				});
			}
			modalobj.modal('hide');
		});
		modalobj.modal('show');
	});
};
util.toast = function(msg, redirect, type) {
	if (!type || type == 'success') {
		var toast = mui.toast('<div class="mui-toast-icon"><span class="fa fa-check"></span></div>' + msg);
	} else if (type == 'error') {
		var toast = mui.toast('<div class="mui-toast-icon"><span class="fa fa-exclamation-circle"></span></div>' + msg);
	}
	if (redirect) {
		var timeout = 3;
		var timer = setInterval(function(){
			if (timeout <= 0) {
				clearInterval(timer);
				location.href = redirect;
				return;
			}
			timeout--;
		}, 1000);
	}
	return toast;
};
util.loading = function(action) {
	var action = action ? action : 'show';
	var loader = {};
	var loaderWapper = $('.js-toast-loading');
	if (loaderWapper.size() <= 0) {
		var loaderWapper = $('<div class="mui-toast-container mui-active js-toast-loading"><div class="mui-toast-message"><div class="mui-toast-icon"><span class="fa fa-spinner fa-spin"></span></div>加载中</div></div>');
	}
	loader.show = function() {
		document.body.appendChild(loaderWapper[0]);
	};
	loader.close = function() {
		loaderWapper.remove();
	};
	loader.hide = function() {
		loaderWapper.remove();
	};
	if (action == 'show') {
		loader.show();
	} else if (action == 'close') {
		loader.close();
	}
	return loader;
};
/**
redirect 要跳转的目标链接，如果链接中含有##auto，系统则会自动跳转
**/
util.message = function(msg, redirect, type, desc){
	var messageWapper = $('<div>' + window.util.templates['message.html'] + '</div>');
	messageWapper.attr('class', 'mui-content fadeInUpBig animated ' + mui.className('backdrop'));
	messageWapper.on(mui.EVENT_MOVE, mui.preventDefault);
	messageWapper.css('background-color', '#efeff4');
	if (desc) {
		messageWapper.find('.mui-desc').html(desc);
	}
	if (redirect) {
		var url = redirect.replace('##auto')
		messageWapper.find('.mui-btn-success').attr('href', url);
		if (redirect.indexOf('##auto') > -1) {
			var timeout = 5;
			var timer = setInterval(function(){
				if (timeout <= 0) {
					clearInterval(timer);
					location.href = url;
					return;
				}
				messageWapper.find('.mui-btn-success').html(timeout + '秒后自动跳转');
				timeout--;
			}, 1000);
		}
	}
	messageWapper.find('.mui-btn-success').click(function(){
		if (redirect) {
			var url = redirect.replace('##auto')
			location.href = url;
		} else {
			history.go(-1);
		}
		return;
	});
	if (!type || type == 'success') {
		messageWapper.find('.title').html(msg);
		messageWapper.find('.mui-message-icon span').attr('class', 'mui-msg-success');
	} else if(type = 'error') {
		messageWapper.find('.title').html(msg);
		messageWapper.find('.mui-message-icon span').attr('class', 'mui-msg-error');
	}
	document.body.appendChild(messageWapper[0]);
};
util.alert = function(message, title, btnValue, callback) {
	return mui.alert(message, title, btnValue, callback);
};
util.confirm = function(message, title, btnArray, callback) {
	return mui.confirm(message, title, btnArray, callback);
};
util.pay = function(option) {
	var defaultOptions = {
		enabledMethod : [],
		defaultMethod : 'wechat',
		payMethod : 'wechat',
		orderTitle : '',
		orderTid : '',
		success : function(){},
		faild : function() {},
		finish : function() {},
	};
	option = $.extend({}, defaultOptions, option);
	if (!option.orderFee || option.orderFee <= 0) {
		util.toast('请确认支付金额', '', 'error');
		return;
	}
	if (!option.defaultMethod && option.payMethod) {
		option.defaultMethod = option.payMethod;
	}
	var CLASS_ACTIVE = mui.className('active'), CLASS_BACKDROP = mui.className('backdrop');
	var paypanel = $('#pay-detail-modal').size() > 0 ? $('#pay-detail-modal') : $('<div class="mui-modal ' + CLASS_ACTIVE + ' js-pay-detail-modal" id="pay-detail-modal"></div>');
	var removeBackdropTimer;
	var fixedModalScroll = function(isScroll) {
		if (isScroll) {
			$('.mui-content')[0].setAttribute('style', 'overflow:hidden;');
			document.body.setAttribute('style', 'overflow:hidden;');
		} else {
			$('.mui-content')[0].setAttribute('style', '');
			document.body.setAttribute('style', '');
		}
	};
	var backdrop = (function() {
		var element = document.createElement('div');
		element.classList.add(CLASS_BACKDROP);
		element.addEventListener(mui.EVENT_MOVE, mui.preventDefault);
		element.addEventListener('click', function(e) {
			if (paypanel) {
				paypanel.remove();
				$(backdrop).remove();
				document.body.setAttribute('style', ''); //webkitTransitionEnd有时候不触发？
				return false;
			}
		});
		return element;
	}());
	var switchmodal = function(modal) {
		if (modal == 'main') {
			paypanel.find('.js-main-modal').show().addClass('fadeInRight animated');
			paypanel.find('.js-switch-pay-modal').hide();
			paypanel.find('.js-switch-modal').hide();
		} else if (modal == 'pay') {
			paypanel.find('.js-main-modal').hide();
			paypanel.find('.js-switch-pay-modal').show().addClass('fadeInRight animated');
			paypanel.find('.js-switch-modal').show();
		}
	};
	var dopaywechat = function() {
		$.post('index.php?i='+window.sysinfo['uniacid']+'&j='+window.sysinfo['acid']+'&c=entry&m=core&do=pay', {
			'method' : option.payMethod,
			'tid' : option.orderTid,
			'title' : option.orderTitle,
			'fee' : option.orderFee,
			'module' : option.module,
		}, function(result) {
			util.loading().hide();
			result = $.parseJSON(result);
			if (result.message.errno) {
				var error = {'errno' : result.message.errno, 'message' : result.message.message};
				option.fail(error);
				option.complete(error);
				return;
			}
			payment = result.message.message;
			WeixinJSBridge.invoke('getBrandWCPayRequest', {
				'appId' : payment.appId,
				'timeStamp': payment.timeStamp,
				'nonceStr' : payment.nonceStr,
				'package' : payment.package,
				'signType' : payment.signType,
				'paySign' : payment.paySign
			}, function(res) {
				if(res.err_msg == 'get_brand_wcpay_request:ok') {
					var error = {'errno' : 0, 'message' : res.err_msg};
					option.success(error);
					option.complete(error);
				} else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
					var error = {'errno' : -1, 'message' : res.err_msg};
					option.complete(error);
				} else {
					var error = {'errno' : -2, 'message' : res.err_msg};
					option.fail(error);
					option.complete(error);
				}
			});
		});
	};
	var dopayalipay = function() {
		util.loading().hide();
		$.post('index.php?i='+window.sysinfo['uniacid']+'&j='+window.sysinfo['acid']+'&c=entry&m=core&do=pay', {
			'method' : option.payMethod,
			'tid' : option.orderTid,
			'title' : option.orderTitle,
			'fee' : option.orderFee,
			'module' : option.module,
		}, function(result) {
			util.loading().hide();
			result = $.parseJSON(result);
			require(['../payment/alipay/ap.js'],function(){
				_AP.pay(result.message.message);
			});
		});
	};
	var dopay = function(){
		util.loading().hide();
		$.post('index.php?i='+window.sysinfo['uniacid']+'&j='+window.sysinfo['acid']+'&c=entry&m=core&do=pay', {
			'method' : option.payMethod,
			'tid' : option.orderTid,
			'title' : option.orderTitle,
			'fee' : option.orderFee,
			'module' : option.module,
		}, function(result) {
			result = $.parseJSON(result);
			util.loading().hide();
			location.href = result.message.message;
			return;
		});
	};
	util.loading().show();
	//如果没有开启多个支付则直接进行支付，否则弹出选择支付方式框
	if (option.enabledMethod && option.enabledMethod.length > 1) {
		$.post('index.php?i='+window.sysinfo['uniacid']+'&j='+window.sysinfo['acid']+'&c=entry&m=core&do=paymethod', {
			'module' : option.module,
			'tid' : option.orderTid,
			'title' : option.orderTitle,
			'fee' : option.orderFee,
		}, function(html) {
			util.loading().hide();
			paypanel.html(html);
			backdrop.setAttribute('style', '');
			$(document.body).append(paypanel);
			$(document.body).append(backdrop);
			fixedModalScroll(true);
			
			paypanel.find('.js-switch-modal').click(function(){
				switchmodal('main');
			});
			paypanel.find('.js-switch-pay').click(function(){
				switchmodal('pay');
			});
			paypanel.find('.js-switch-pay-close').click(function(){
				paypanel.remove();
				$(backdrop).remove();
				document.body.setAttribute('style', ''); //webkitTransitionEnd有时候不触发？
			});
			paypanel.find('.js-order-title').html(option.orderTitle);
			paypanel.find('.js-pay-fee').html(option.orderFee);
			//处理参数中强制关闭某些支付，及默认支付
			if (paypanel.find('.js-switch-pay-modal li').size() > 0) {
				if (option.enabledMethod && option.enabledMethod.length > 0) {
					paypanel.find('.js-switch-pay-modal li').each(function(){
						if ($.inArray($(this).data('method'), option.enabledMethod) == -1) {
							$(this).remove();
						}
					});
				} else {
					paypanel.find('.js-switch-pay-modal li').each(function(){
						option.enabledMethod.push($(this).data('method'));
					});
				}
				if (option.defaultMethod && $.inArray(option.defaultMethod, option.enabledMethod) > -1) {
					var defaultMethod = paypanel.find('.js-switch-pay-modal li[data-method='+option.defaultMethod+']');
				} else {;
					var defaultMethod = $(paypanel.find('.js-switch-pay-modal li:first'));
				}
				if (defaultMethod.size() == 0) {
					util.toast('暂无有效支付方式');
					paypanel.remove();
					$(backdrop).remove();
					document.body.setAttribute('style', ''); //webkitTransitionEnd有时候不触发？
					return false;
				}
				paypanel.find('.js-pay-default-method').html(defaultMethod.data('title'));
				paypanel.find('.js-dopay').click(function(){
					dopay(defaultMethod.data('title'), defaultMethod.data('method'));
				});
			} else {
				util.toast('暂无有效支付方式');
				paypanel.remove();
				$(backdrop).remove();
				document.body.setAttribute('style', ''); //webkitTransitionEnd有时候不触发？
				return false;
			}
		});
	} else {
		if (typeof typeof ('dopay' + option.payMethod) == 'function') {
			eval('dopay' + option.payMethod + '();');
		} else {
			dopay();
		}
	}
	return true;
};
util.poppicker = function(option, callback) {
	require(['mui.datepicker'], function(){
		mui.ready(function(){
			var picker = new mui.PopPicker({layer: (option.layer || 1)});
			picker.setData(option.data);
			picker.show(function(items){
				if($.isFunction(callback)){
					callback(items);
				}
				picker.dispose();
			});
		});
	});
}
/**
 * 弹出地区选择
 * @params selector dom对象或选择器
 * @option {layer:层级, data:数据[{value:?,text:?,children: [...]},...], type:date|time|datetime}
 */
util.districtpicker = function(callback){
	require(['mui.districtpicker'], function(cityData3){
		mui.ready(function(){
			var option = {layer:3, data : cityData3};
			util.poppicker(option, callback);
		});
	});
};

/**
 * 弹出选择框
 * @params selector dom对象或选择器
 * @option {layer:层级, data:数据[{value:?,text:?,children: [...]},...], type:date|time|datetime}
 */
util.datepicker = function(option, callback){
	require(['mui.datepicker'], function(){
		mui.ready(function(){
			var picker;
			picker = new mui.DtPicker(option);
			picker.show(function(items){
				if($.isFunction(callback)){
					callback(items);
				}
				picker.dispose();
			});
		});
	});
};
util.querystring = function(name){
	var result = location.search.match(new RegExp("[\?\&]" + name+ "=([^\&]+)","i"));
	if (result == null || result.length < 1){
		return "";
	}
	return result[1];
}

util.tomedia = function(src, forcelocal){
	if(!src) {
		return '';
	}
	if(src.indexOf('./addons') == 0) {
		return window.sysinfo.siteroot + src.replace('./', '');
	}
	if(src.indexOf(window.sysinfo.siteroot) != -1 && src.indexOf('/addons/') == -1) {
		src = src.substr(src.indexOf('images/'));
	}
	if(src.indexOf('./resource') == 0) {
		src = 'app/' + src.substr(2);
	}
	var t = src.toLowerCase();
	if(t.indexOf('http://') != -1 || t.indexOf('https://') != -1 ) {
		return src;
	}
	if(forcelocal || !window.sysinfo.attachurl_remote) {
		src = window.sysinfo.attachurl_local + src;
	} else {
		src = window.sysinfo.attachurl_remote + src;
	}
	return src;
};

util.sendCode = function(mobile, option) {
	var defaultOptions = {
		'btnElement' : '',
		'showElement' : '',
		'showTips' : '%s秒后重新获取',
		'btnTips' : '重新获取验证码',
		'successCallback' : arguments[3],
	};
	if (typeof arguments[1] != 'object') { 
		var selector = mobile;
		var mobile = option;
		option = {
			'btnElement' : $(selector),
			'showElement' : $(selector),
			'showTips' : '%s秒后重新获取',
			'btnTips' : '重新获取验证码',
			'successCallback' : arguments[2],
		};
	} else {
		option = $.extend({}, defaultOptions, option);
	}
	if(!mobile) {
		return option.successCallback('1', '请填写正确的手机号');
	}
	if(!/^1[3|4|5|7|8][0-9]{9}$/.test(mobile)) {
		return option.successCallback('1', '手机格式错误');
	}
	var downcount = 60;
	option.showElement.html(option.showTips.replace('%s', downcount));
	option.showElement.attr('disabled', true);
	var timer = setInterval(function(){
		downcount--;
		if(downcount <= 0){
			clearInterval(timer);
			downcount = 60;
			option.showElement.html(option.btnTips);
			option.showElement.attr('disabled', false);
		}else{
			option.showElement.html(option.showTips.replace('%s', downcount));
		}
	}, 1000);
	var params = {};
	params.receiver = mobile;
	params.uniacid = window.sysinfo.uniacid;
	$.post('../web/index.php?c=utility&a=verifycode', params).success(function(dat){
		if (dat == 'success') {
			return option.successCallback('0', '验证码发送成功');
		} else {
			return option.successCallback('1', dat);
		}
	});
}
util.loading1 = function() {
	var loadingid = 'modal-loading';
	var modalobj = $('#' + loadingid);
	if(modalobj.length == 0) {
		$(document.body).append('<div id="' + loadingid + '" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
		modalobj = $('#' + loadingid);
		html = 
			'<div class="modal-dialog">'+
			'	<div style="text-align:center; background-color: transparent;">'+
			'		<img style="width:48px; height:48px; margin-top:100px;" src="../attachment/images/global/loading.gif" title="正在努力加载...">'+
			'	</div>'+
			'</div>';
		modalobj.html(html);
	}
	modalobj.modal('show');
	modalobj.next().css('z-index', 999999);
	return modalobj;
};

util.loaded1 = function(){
	var loadingid = 'modal-loading';
	var modalobj = $('#' + loadingid);
	if(modalobj.length > 0){
		modalobj.modal('hide');
	}
};

util.cookie = {
	'prefix' : '',
	// 保存 Cookie
	'set' : function(name, value, seconds) {
		expires = new Date();
		expires.setTime(expires.getTime() + (1000 * seconds));
		document.cookie = this.name(name) + "=" + escape(value) + "; expires=" + expires.toGMTString() + "; path=/";
	},
	// 获取 Cookie
	'get' : function(name) {
		cookie_name = this.name(name) + "=";
		cookie_length = document.cookie.length;
		cookie_begin = 0;
		while (cookie_begin < cookie_length)
		{
			value_begin = cookie_begin + cookie_name.length;
			if (document.cookie.substring(cookie_begin, value_begin) == cookie_name)
			{
				var value_end = document.cookie.indexOf ( ";", value_begin);
				if (value_end == -1)
				{
					value_end = cookie_length;
				}
				return unescape(document.cookie.substring(value_begin, value_end));
			}
			cookie_begin = document.cookie.indexOf ( " ", cookie_begin) + 1;
			if (cookie_begin == 0)
			{
				break;
			}
		}
		return null;
	},
	// 清除 Cookie
	'del' : function(name) {
		var expireNow = new Date();
		document.cookie = this.name(name) + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" + "; path=/";
	},
	'name' : function(name) {
		return this.prefix + name;
	}
};//end cookie

util.agent = function() {
	var agent = navigator.userAgent;
	var isAndroid = agent.indexOf('Android') > -1 || agent.indexOf('Linux') > -1;
	var isIOS = !!agent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
	if (isAndroid) {
		return 'android';
	} else if (isIOS) {
		return 'ios';
	} else {
		return 'unknown'
	}
};

util.removeHTMLTag = function(str) {
	if(typeof str == 'string'){
		str = str.replace(/<script[^>]*?>[\s\S]*?<\/script>/g,'');
		str = str.replace(/<style[^>]*?>[\s\S]*?<\/style>/g,'');
		str = str.replace(/<\/?[^>]*>/g,'');
		str = str.replace(/\s+/g,'');
		str = str.replace(/&nbsp;/ig,'');
		return str;
	}
};

util.card = function() {
	$.post('./index.php?c=utility&a=card', {'uniacid' : window.sysinfo['uniacid'],'acid' : window.sysinfo['acid']}, function (data) {
		util.loading().hide();
		var data = $.parseJSON(data);
		if (data.message.errno == 0) {
			util.message('没有开通会员卡功能', '', 'info');
			return false;
		}
		if (data.message.errno == 1) {
			wx.ready(function(){
				wx.openCard({
					cardList:[
						{
							cardId : data.message.message['card_id'],
							code :  data.message.message['code']
						}
					]
				});
			});
		}
		if (data.message.errno == 2) {
			location.href = "./index.php?i="+window.sysinfo['uniacid']+"&c=mc&a=card&do=mycard";
		}
		if (data.message.errno == 3) {
			alert('由于会员卡升级到微信官方会员卡，需要您重新领取并激活会员卡');
			wx.ready(function(){
				wx.addCard({
					cardList:[
						{
							cardId : data.message.message.card_id,
							cardExt : data.message.message.card_ext
						}
					],
					success: function (res) {
					}
				});
			});
		}
	});
};
if (typeof define === "function" && define.amd) {
	define(function(){
		return util;
	});
} else {
	window.util = util;
}
})(window);
;(function (templates, undefined) {
  templates["avatar.preview.html"] = "<div class=\"fadeInDownBig animated js-avatar-preview avatar-preview\" style=\"position:relative; width:100%;z-index:9999\"><img src=\"\" alt=\"\" class=\"cropper-hidden\"><div class=\"bar-action mui-clearfix\"><a href=\"javascript:;\" class=\"mui-pull-left js-cancel\">取消</a> <a href=\"javascript:;\" class=\"mui-pull-right mui-text-right js-submit\">选取</a></div></div>";
  templates["image.preview.html"] = "<div class=\"bar-action mui-clearfix\"><a href=\"javascript:;\" class=\"mui-pull-left js-cancel\">取消</a> <a href=\"javascript:;\" class=\"mui-pull-right mui-text-right js-submit\">删除</a></div>";
  templates["message.html"] = "<div class=\"mui-content-padded\"><div class=\"mui-message\"><div class=\"mui-message-icon\"><span></span></div><h4 class=\"title\"></h4><p class=\"mui-desc\"></p><div class=\"mui-button-area\"><a href=\"javascript:;\" class=\"mui-btn mui-btn-success mui-btn-block\">确定</a></div></div></div>";
})(this.window.util.templates = this.window.util.templates || {});