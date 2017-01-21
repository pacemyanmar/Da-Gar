
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the body of the page. From here, you may begin adding components to
 * the application, or feel free to tweak this setup for your needs.
 */

//Vue.component('example', require('./components/Example.vue'));


// Form reset method from stackoverflow
// http://stackoverflow.com/questions/680241/resetting-a-multi-stage-form-with-jquery
global.resetForm = function (form) {
    form.find('input:text, input:password, input:file, select, textarea').val('');
    form.find('input:radio, input:checkbox')
         .removeAttr('checked').removeAttr('selected');
}

global.sendAjax = function (url,data) {
	var request = $.ajax({
		  url: url,
		  method: "POST",
		  data: data
		});

		request.done(function( msg ) {
			alert(msg.message);
		});

		request.fail(function( jqXHR, textStatus ) {
			alert(jqXHR.responseJSON.message);
		});

		request.always(function(){
			$('.loading').addClass("hidden");
		});
}

jQuery(document).ready(function() {
 		jQuery.ajaxSetup({
			headers:
			{ 'X-CSRF-TOKEN': Laravel.csrfToken }
		});
	var offset = 150;

	var duration = 300;

	jQuery(window).scroll(function() {

	if (jQuery(this).scrollTop() > offset) {

	jQuery('.btn-float').fadeIn(duration);

	} else {

	jQuery('.btn-float').fadeOut(duration);

	}

	});


	jQuery('.btn-float-to-up').click(function(event) {

	event.preventDefault();

	jQuery('html, body').animate({scrollTop: 0}, duration);

	return false;

	})

});