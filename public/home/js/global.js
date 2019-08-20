jQuery(function($) {

	"use strict";

	//menu
	$('.cmn-toggle-switch').on('click', function(e){
		$(this).toggleClass('active');
		$(this).parents('header').find('.toggle-block').slideToggle();
		e.preventDefault();
	});
	$('.main-nav .menu-toggle').on('click', function(e){
		$(this).closest('li').addClass('active').siblings('.active').removeClass('active');
		$(this).closest('li').siblings('.parent').find('ul').slideUp();
		$(this).parent().siblings('ul').slideToggle();
		e.preventDefault();
	});
	$('.main-nav .menu-toggle-inner').on('click', function(e){
		$(this).closest('li').addClass('active').siblings('.active').removeClass('active');
		$(this).closest('li').siblings('li').find('ul').slideUp();
		$(this).parent().siblings('ul').slideToggle();
		e.preventDefault();
	});

    //Tabs
	var tabFinish = 0;
	$('.nav-tab-item').on('click', function(){
	    var $t = $(this);
	    if(tabFinish || $t.hasClass('active')) return false;
	    tabFinish = 1;
	    $t.closest('.nav-tab').find('.nav-tab-item').removeClass('active');
	    $t.addClass('active');
	    var index = $t.parent().parent().find('.nav-tab-item').index(this);
	    $t.parents('.tab-nav-wrapper').find('.tab-select select option:eq('+index+')').prop('selected', true);
	    $t.closest('.tab-wrapper').find('.tab-info:visible').fadeOut(500, function(){
	    	var $tabActive  = $t.closest('.tab-wrapper').find('.tab-info').eq(index);
	    	$tabActive.css('display','block').css('opacity','0');
	    	$tabActive.animate({opacity:1});
			tabFinish = 0;
	    });
	});
	$('.tab-select select').on('change', function(){
	    var $t = $(this);
	    if(tabFinish) return false;
	    tabFinish = 1;    
	    var index = $t.find('option').index($(this).find('option:selected'));
	    $t.closest('.tab-nav-wrapper').find('.nav-tab-item').removeClass('active');
	    $t.closest('.tab-nav-wrapper').find('.nav-tab-item:eq('+index+')').addClass('active');
	    $t.closest('.tab-wrapper').find('.tab-info:visible').fadeOut(500, function(){
	    	var $tabActive  = $t.closest('.tab-wrapper').find('.tab-info').eq(index);
	    	$tabActive.css('display','block').css('opacity','0');
	    	$tabActive.animate({opacity:1});
	    	 tabFinish = 0;
	    });
	});
});