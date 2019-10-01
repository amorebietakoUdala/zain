import '../../css/event/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import 'tableexport.jquery.plugin/tableExport';
import 'jquery-ui';

$(document).ready(function(){
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
	var $table = $('#taula');
	$(function () {
	    $('#toolbar').find('select').change(function () {
		$table.bootstrapTable('destroy').bootstrapTable({
		    exportDataType: $(this).val(),
		});
	    });
	});
});