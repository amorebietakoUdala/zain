import '../../css/mevent/new.css';

import $ from 'jquery';

function View() {
  var dom = {
    form: $('form[name="monitorizable_event_form"]'),
    btnCancel: $('.js-btn_cancel'),
  };
  function onBtnCancelClick() { 
    dom.btnCancel.on('click', function (e) {
	e.preventDefault();
	history.back();
    });
  }
  return {
    onBtnCancelClick: onBtnCancelClick,
  };
}

$(document).ready(function(){
	var view = View();
view.onBtnCancelClick();
});
