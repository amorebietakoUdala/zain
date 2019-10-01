import '../../css/dashboard/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import 'tableexport.jquery.plugin/tableExport';
import 'jquery-ui';

function View() {
  var dom = {
    btnStatusSelection: $('.btnStatusSelection'),
    btnResetFilter: $('#btnResetFilter'),
    table: $('#taula'),
    tableSearchInput: $('.bootstrap-table').find('.form-control'),
  };
  function btnStatusSelectionClick() { 
    dom.btnStatusSelection.on('click', function (e) {
	e.preventDefault();
	console.log('btnStatusSelection Clicked!');
	var filter = $(e.currentTarget).find('i.fa').attr('title');
	$(dom.tableSearchInput).val(filter);
	$(dom.table).bootstrapTable('resetSearch', filter );
    });
  }
  function btnResetFilterClick() { 
    dom.btnResetFilter.on('click', function (e) {
	e.preventDefault();
	console.log('btnbtnResetFilter Clicked!');
	$(dom.table).bootstrapTable('resetSearch', '' );
    });
  }
  return {
    btnStatusSelectionClick: btnStatusSelectionClick,
    btnResetFilterClick: btnResetFilterClick
  };
}

$(document).ready(function() {
	console.log("List view!!!");
	$('#taula').bootstrapTable({
	    cache : false,
		showExport: false,
		exportDataType: 'all',
		showColumns: false,
		pagination: true,
		search: true,
		striped: true,
		sortStable: true,
		pageSize: 100,
		pageList: [10,25,50,100],
		sortable: true,
		locale: $('html').attr('lang')+'-'+$('html').attr('lang').toUpperCase()
	});
	
	var $table = $('#taula');
	$(function () {
	    $('#toolbar').find('select').change(function () {
		$table.bootstrapTable('destroy').bootstrapTable({
		    exportDataType: $(this).val(),
		});
	    });
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
    
});