import '../../css/mevent/try.scss';

import $ from 'jquery';


function View() {
  var dom = {
    form: $('form[name="event_try_form"]'),
    btnSave: $('#event_try_form_save'),
    btnTest: $('#event_try_form_test'),
    btnCancel: $('#js-btn_cancel')
  };
  function onBtnSaveClick() { 
    dom.btnSave.on('click', function (e) {
	e.preventDefault();
	$(dom.form).attr("action",document.location.href+"/save");
	dom.form.submit();
    });
  }
  function onBtnCancelClick() { 
    dom.btnCancel.on('click', function (e) {
	e.preventDefault();
	var url = $(e.currentTarget).data('url');
	document.location.href= url;
    });
  }
  function onBtnTestClick() { 
    dom.btnTest.on('click', function (e) {
	e.preventDefault();
	dom.form.submit();
    });
  }
  return {
    onBtnSaveClick: onBtnSaveClick,
    onBtnTestClick: onBtnTestClick,
    onBtnCancelClick: onBtnCancelClick
  };
}

$(document).ready(function(){
	var view = View();
	view.onBtnSaveClick();
	view.onBtnTestClick();
	view.onBtnCancelClick();
});
