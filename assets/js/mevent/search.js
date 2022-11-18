import '../../css/mevent/search.css';

import $ from 'jquery';

import 'bootstrap-table';
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import tempusDominus from '@eonasdan/tempus-dominus';
import customDateFormat from '@eonasdan/tempus-dominus/dist/plugins/customDateFormat';


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

$(document).ready(function(){
	let current_locale = $('html').attr('lang') + '-' + $('html').attr('lang').toUpperCase();
	tempusDominus.extend(customDateFormat);
	const options = {
		display: {
			buttons: {
				close: true,
				clear: true,
			},
			components: {
				decades: false,
				year: true,
				month: true,
				date: true,
				clock: true,
			},
		},
		localization: {
			locale: current_locale,
			dayViewHeaderFormat: { month: 'long', year: 'numeric' },
			format: 'yyyy-MM-dd HH:mm',
		},
	};
	let datepicker1 = new tempusDominus.TempusDominus(document.getElementById('event_search_form_dateFrom'), options);
	let datepicker2 = new tempusDominus.TempusDominus(document.getElementById('event_search_form_dateTo'), options);
	$('.js-showDetails').on('click',function(e){
		e.preventDefault();
		var id = e.currentTarget.id;
		var overlay = id.replace('tr','ov');
		$('#'+overlay).fadeIn(300);
		$('.bootstrap-table').hide();
	});
	$('.close').click(function(e) {
		var number = e.target.id.slice(2);
		$('#ov'+number).fadeOut(300);
		$('.bootstrap-table').show();
	});
	$('.overlay').hide();
	$('#taula').bootstrapTable({
		cache : false,
		showExport: false,
		exportDataType: 'all',
		showColumns: false,
		pagination: true,
		search: true,
		striped: true,
		sortStable: true,
		pageSize: 10,
		pageList: [10,25,50,100],
		sortable: true,
		locale: $('html').attr('lang')+'-'+$('html').attr('lang').toUpperCase(),
	});
	
	var $table = $('#taula');
	$(function () {
	    $('#toolbar').find('select').change(function () {
			$table.bootstrapTable('destroy').bootstrapTable({
			exportDataType: $(this).val(),
		});
	    });
	});
	var view = View();
	view.onBtnSaveClick();
	view.onBtnResetClick();
	view.onBtnCancelClick();
	$('.js-showDetails').on('click',function(e){
		e.preventDefault();
		var id = e.currentTarget.id;
		var overlay = id.replace('tr','ov');
		$('#'+overlay).fadeIn(300);
		$('.bootstrap-table').hide();
	});
	$('.close').click(function(e) {
		var number = e.target.id.slice(2);
		$('#ov'+number).fadeOut(300);
		$('.bootstrap-table').show();
	});
});