/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
/*
infiniteslide.js v2
version: 2.0.1
Author: T.Morimoto

Copyright 2017, T.Morimoto
* Free to use and abuse under the MIT license.
* http://www.opensource.org/licenses/mit-license.php

https://github.com/woodroots/infiniteslidev2
*/
(function($){
	$(window).on('load', function() {
	    window.loaded = true;
	});
	$(function(){
		$.fn.infiniteslide = function(options){
			var settings = $.extend({
				'speed': 100,
				'direction': 'up', //up/down/left/right
				'pauseonhover': true,
				'responsive': false,
				'clone': 1
			},options);

			var setCss = function(obj,direction){
				$(obj).wrap('<div class="infiniteslide_wrap"></div>').parent().css({
					overflow: 'hidden'
				});

				if(direction == 'up' || direction == 'down'){
					var d = 'column';
				} else {
					var d = 'row';
				}

				$(obj).css({
					display: 'flex',
					flexWrap: 'nowrap',
					alignItems: 'center',
					'-ms-flex-align': 'center',
					flexDirection: d
				}).children().css({
						flex: 'none',
						display: 'block'
					});
			}

			var setClone = function(obj,clone){
				var $clone = $(obj).children().clone(true).addClass('infiniteslide_clone');
				// set to 1 for gap
				var i = 0;
				while(i <= clone){
					$clone.clone(true).appendTo($(obj));
					i++;
				}
			}

			var getWidth = function(obj){
				var w = 0;
				$(obj).children(':not(.infiniteslide_clone)').each(function(key,value){
					w = w + $(this).outerWidth(true);
				});
				return w;
			}
			var getHeight = function(obj){
				var h = 0;
				$(obj).children(':not(.infiniteslide_clone)').each(function(key,value){
					h = h + $(this).outerHeight(true);
				});
				return h;
			}


			var getSpeed = function(l,s){
				return l / s;
			}
			var getNum = function(obj,direction){
				var num = 0;
				if(direction == 'up' || direction == 'down'){
					num = getHeight(obj);
				} else {
					num = getWidth(obj);
				}
				return num;
			}

			var getTranslate = function(num,direction){
				if(direction == 'up' || direction == 'down'){
					var i = '0,-' + num + 'px,0';
				} else {
					var i = '-' + num + 'px,0,0';
				}
				return i;
			}

			var setAnim = function(obj,id,direction,speed){
				var num = getNum(obj,direction);
				if(direction == 'up' || direction == 'down'){
					$(obj).parent('.infiniteslide_wrap').css({
						height: '100%'
					});
				}
				var i = getTranslate(num,direction);

				$(obj).attr('data-style','infiniteslide' + id);

				var css = '@keyframes infiniteslide' + id + '{' + 'from {transform:translate3d(0,0,0);}' + 'to {transform:translate3d(' + i + ');}' + '}';
				$('<style />').attr('id','infiniteslide' + id + '_style').html(css).appendTo('head');

				if(direction == 'right' || direction == 'down'){
					var reverse = ' reverse';
				} else {
					var reverse = '';
				}

				$(obj).css({
					animation: 'infiniteslide' + id + ' ' + getSpeed(num,speed) + 's linear 0s infinite' + reverse
				});
			}
			var setStop = function(obj){
				$(obj).on('mouseenter',function(){
					$(this).css({
						animationPlayState: 'paused'
					});
				}).on('mouseleave',function(){
					$(this).css({
						animationPlayState: 'running'
					});
				});
			}

			var setResponsive = function(obj,direction){
				var num = getNum(obj,direction);
				var i = getTranslate(num,direction);
				return i;
			};



			return this.each(function(key,value){
				var $this = $(this);
				var num = Date.now() + Math.floor(10000*Math.random()).toString(16);
				if(settings.pauseonhover == true){
					setStop($this);
				}
				var _onload = function(){
					setCss($this,settings.direction);
					setClone($this,settings.clone);
					setAnim($this,num,settings.direction,settings.speed);

					if(settings.responsive){
						$(window).on('resize',function(){
							var i = setResponsive($this,settings.direction);
							var styleid = $this.attr('data-style');
							var stylehtml = $('#' + styleid + '_style').html();

							var stylehtml_new = stylehtml.replace(/to {transform:translate3d\((.*?)\)/,'to {transform:translate3d(' + i + ')');
							$('#' + styleid + '_style').html(stylehtml_new);

						});
					}
				};

				if (window.loaded) {
					_onload();
				} else {
					$(window).on('load', _onload);
				}
			});

		}
	});
})(jQuery);