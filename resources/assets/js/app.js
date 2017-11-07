
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./settings');
hyperform = require('hyperform').default;
window.hyperform = hyperform;
global.hyperform = hyperform;
hyperform.add_translation("mm",{
    TextTooLong: 'စာလုံးအေရအတွက်အား %l အောက်သို့ လျှော့ချပါ (လောလောဆယ် %l စာလုံးအား အသုံးပြုထားသည်).',
    TextTooShort:"စာလုံးအေရအတွက်အား %l ထက်ပိုသုံးပါ (လောလောဆယ် %l စာလုံးအား အသုံးပြုထားသည်).",
    ValueMissing: 'ဒီအကွက်ကို ဖြည့်ပေးပါ။',
    CheckboxMissing: 'ဆက်လုပ်လိုလျှင် ဒီအကွက်အား အမှန်ခြစ်ပါ။',
    RadioMissing: 'ဒီထဲက တစ်ခုခုကို ရွေးပါ။',
    FileMissing: 'ဖိုင်တစ်ဖိုင် ရွေးပေးပါ။',
    SelectMissing: 'စာရင်းထဲမှ တစ်ခုခုအား ရွေးပါ။',
    InvalidEmail: 'အီးမေးလ်လိပ်စာ ဖြည့်ပါ။',
    InvalidURL: 'URL ဖြည့်ပါ။',
    PatternMismatch: 'သတ်မှတ်ပုံစံအတိုင်း ဖြည့်သွင်းပါ။',
    PatternMismatchWithTitle: 'သတ်မှတ်ပုံစံအတိုင်း ဖြည့်သွင်းပါ။: %l.',
    NumberRangeOverflow: '%l ထက်မများသော တန်ဖိုးတစ်ခုရွေးပါ။',
    DateRangeOverflow: '%l အောက် မငယ်သော တန်ဖိုးတစ်ခုရွေးပါ။',
    TimeRangeOverflow: '%l ထက်နောက်မကျသော အချိန်ရွေးပါ။',
    NumberRangeUnderflow: '%l အောက် မငယ်သော တန်ဖိုးတစ်ခု ရွေးပါ။',
    DateRangeUnderflow: '%l ထက် မစောသော တစ်ရက် ရွေးပါ။',
    TimeRangeUnderflow: '%l ထက် မစောသော အချိန်တစ်ခု ရွေးပါ။',
    StepMismatch: 'မှန်ကန်သော တန်ဖိုးအား ဖြည့်ပါ။ အနီးစပ်ဆုံးမှာ %l နှင့် %l တို့ဖြစ်သည်။',
    StepMismatchOneValue: 'မှန်ကန်သော တန်ဖိုးအား ဖြည့်ပါ။ အနီးစပ်ဆုံးမှာ %l ဖြစ်သည်',
    BadInputNumber: 'ဂဏန်း တစ်ခုဖြစ်ည့သွင်းပါ။'
});

window.Vue = require('vue');
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('data-table', require('./components/DataTable.vue'));


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
		return request;
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