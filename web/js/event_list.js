function View() {
  var dom = {
    form: $('form[name="event_search_form"]'),
    btnSave: $('#event_search_form_save'),
    btnReset: $('#event_search_form_reset'),
  };
  function onBtnSaveClick() { 
    dom.btnSave.on('click', function (e) {
	e.preventDefault();
	$(dom.form).attr("action",document.location.href+"/save");
//	console.log(dom.form);
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
  return {
    onBtnSaveClick: onBtnSaveClick,
    onBtnResetClick: onBtnResetClick
  };
}

function showDialog (id) {
    console.log(id);
    $('#'+id).fadeIn(300);
    $('.bootstrap-table').hide();
}
function hideDialog (e) {
    number = e.target.id.slice(2);
    console.log(e.target.id);
    console.log(number);
    $('#ov'+number).fadeOut(300);
}
