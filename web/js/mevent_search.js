function View() {
  var dom = {
    form: $('form[name="event_search_form"]'),
    btnSave: $('#event_search_form_save'),
    btnReset: $('#event_search_form_reset'),
    btnCancel: $('#btnCancel'),
  };
  function onBtnSaveClick() { 
    dom.btnSave.on('click', function (e) {
	e.preventDefault();
	$(dom.form).attr("action",document.location.href+"/save");
	dom.form.submit();
    });
  }
  function onBtnResetClick() { 
    dom.btnReset.on('click', function (e) {
	e.preventDefault();
	console.log('Limpiar');
	$('form[name="event_search_form"] input').not('input[type="hidden"]').val('');
    });
  }
  function onBtnCancelClick() { 
    dom.btnCancel.on('click', function (e) {
	e.preventDefault();
	console.log('Cancelar');
	var url = $(e.currentTarget).data('url');
	document.location.href=url;
    });
  }
  return {
    onBtnSaveClick: onBtnSaveClick,
    onBtnResetClick: onBtnResetClick,
    onBtnCancelClick: onBtnCancelClick,
  };
}