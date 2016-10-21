require.config({
	baseUrl: 'resource/js/app',
	paths: {
		'datetimepicker': '../../components/datetimepicker/jquery.datetimepicker',
		'daterangepicker': '../../components/daterangepicker/daterangepicker',
		'colorpicker': '../../components/colorpicker/spectrum',
		'map': 'http://api.map.baidu.com/getscript?v=2.0&ak=F51571495f717ff1194de02366bb8da9&services=&t=20140530104353',
		'webuploader' : '../../components/webuploader/webuploader.min',
		'fileUploader' : '../../components/fileuploader/fileuploader.min',
		'clockpicker': '../../components/clockpicker/clockpicker.min',
		'district' : '../lib/district',
	},
	shim:{
		'emotion': {
			deps: ['jquery']
		},
		'daterangepicker': {
			exports: '$',
			deps: ['bootstrap', 'moment', 'css!../../components/daterangepicker/daterangepicker.css']
		},
		'datetimepicker' : {
			exports : '$',
			deps: ['jquery', 'css!../../components/datetimepicker/jquery.datetimepicker.css']
		},
		'colorpicker': {
			exports: '$',
			deps: ['css!../../components/colorpicker/spectrum.css']
		},
		'map': {
			exports: 'BMap'
		},
		'fileUploader': {
			deps: ['webuploader', 'css!../../components/webuploader/webuploader.css', 'css!../../components/webuploader/style.css']
		},
		'clockpicker': {
			exports: "$",
			deps: ['css!../../components/clockpicker/clockpicker.min.css', 'bootstrap']
		},
		'district' : {
			exports : "$",
			deps : ['jquery']
		}
	}
});