/** 
adaptation for wordpress by: Pete Scheepens
*/

(function($) {
	$.fn.vrrating = function(op) {
		
		var defaults = {
			/** String vars **/
			plpath : $(this).attr('id').split('|')[2],
			bigStarsPath : '', // path of the icon stars.png
			smallStarsPath : '', // path of the icon small.png
			phpPath : '', // path of the php file jRating.php
			type : 'big', // can be set to 'small' or 'big'
			nonce : 'nononce',
			
			step:false, // if true,  mouseover binded star by star,
			isDisabled:false,
			showRateInfo: true,

			length:5, // number of star to display
			decimalLength : 0, // number of decimals.. Max 3, but you can complete the function 'getNote'
			rateMax : 100, // maximal rate - integer from 0 to 9999 (or more)
			rateInfosX : -45, // relative position in X axis of the info box when mouseover
			rateInfosY : 5, // relative position in Y axis of the info box when mouseover
			
			/** Functions **/
			onSuccess : null,
			onError : null
		}; 
		
		if(this.length>0)
		return this.each(function() {
			var opts = $.extend(defaults, op),    
			newWidth = 0,
			starWidth = 0,
			starHeight = 0,
			bgPath = '';

			if($(this).hasClass('jDisabled') || opts.isDisabled)
				var jDisabled = true;
			else
				var jDisabled = false;

			$(this).width(opts.width);
						
			var average = parseFloat($(this).attr('id').split('_')[0]),
			idBox = parseInt($(this).attr('id').split('_')[1]), // get the id of the box
			theip = parseInt($(this).attr('id').split('|')[1]), // get the id of the box
            randid = parseInt($(this).attr('id').split('|')[3]), // get the id of the box   
			heightRatingContainer = opts.heigth*opts.length, // height of the voted Container
			heightColor = average/opts.rateMax*heightRatingContainer, // height of the red vote color
			remainder = heightRatingContainer-heightColor,
			quotient = 
			$('<div>', 
			{
				'class' : 'jRatingColor',
				css:{
				   top:remainder,
					height:heightColor
				}
			}).appendTo($(this)),
			
			average = 
			$('<div>', 
			{
				'class' : 'jRatingAverage '+idBox,
				css:{
					top:heightRatingContainer,
				   height:heightRatingContainer,
				}
			}).appendTo($(this)),

			 jstar =
			$('<div>', 
			{
				'class' : 'jStar',			   
				css:{
					height:heightRatingContainer,
					width:opts.width,
					background: 'url('+opts.bigStarsPath+') repeat-y'
				}
			}).appendTo($(this));

			$(this).css({height: heightRatingContainer,overflow:'hidden',zIndex:1,position:'relative'});

			if(!jDisabled)
			$(this).bind({
				mouseenter : function(e){
					  var offset = $(this).offset();		
					var bottomY = offset.top+heightRatingContainer;				
					var rate = bottomY-e.pageY;
					var top = heightRatingContainer-rate;
					var therating = Math.round(rate/heightRatingContainer * 100);
					  var tooltip = 
					$('<p>',{
						'class' : 'jRatingInfos',
						html : therating + ' <span class="maxRate">/ '+opts.rateMax+'</span>',
						css : {
							top: (bottomY + rate),
							left: (offset.left+55)
						}
					}).appendTo('body').show();
				},
				mouseover : function(e){
					$(this).css('cursor','pointer');	
				},
				mouseout : function(){
					$(this).css('cursor','default');
					$(".jRatingAverage."+idBox)
					.css({
						top: (heightRatingContainer)
					});
				},
				mousemove : function(e){
					var offset = $(this).offset();		
					var bottomY = offset.top+heightRatingContainer;				
					var rate = bottomY-e.pageY;
					var top = heightRatingContainer-rate;
					var therating = Math.round(rate/heightRatingContainer * 100);
					/*$(".serverResponse").text(idBox + "randid: " +randid + ", e.pageY: " + e.pageY);*/
					
					$(".jRatingAverage."+idBox)
					.css({
						top: (top)
					});
					
					$("p.jRatingInfos")
					.css({
						top: (bottomY - rate)
					})
					.html(therating + ' <span class="maxRate">/ '+opts.rateMax+'</span>');
				},
				
				
				mouseleave : function(){
					 $("p.jRatingInfos").remove();
				},
				click : function(e){
					$(this).unbind().css('cursor','default').addClass('jDisabled');
					var offset = $(this).offset();		
					var bottomY = offset.top+heightRatingContainer;				
					var rate = bottomY-e.pageY;
					var top = heightRatingContainer-rate;
					var therating = Math.round(rate/heightRatingContainer * 100);
					if (opts.showRateInfo) $("p.jRatingInfos").fadeOut('fast',function(){$(this).remove();});
					e.preventDefault();

					
					jQuery.ajax({
						   type : "post",
						   dataType : "json",
						   url : opts.phpPath,
						data : {
						IP : theip,
						idBox : idBox,
						rate : therating,
						nonce : opts.nonce
						},
						   success: function(response) {			
						      $('.vr_serverResponse p').html(response.server);
						   }
						})

				}
			});

			function findRealbottom(obj) {
						  if( !obj ) return 0;
						  return obj.offsetBottom + findRealbottom( obj.offsetParent );
						};
			
			function getNote(relativeX) {
				var noteBrut = parseFloat((relativeX*100/widthRatingContainer)*opts.rateMax/100);
				switch(opts.decimalLength) {
					case 1 :
						var note = Math.round(noteBrut*10)/10;
						break;
					case 2 :
						var note = Math.round(noteBrut*100)/100;
						break;
					case 3 :
						var note = Math.round(noteBrut*1000)/1000;
						break;
					default :
						var note = Math.round(noteBrut*1)/1;
				}
				return note;
			};
		});

	}
})(jQuery);