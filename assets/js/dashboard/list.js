import '../../css/dashboard/list.css';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import striptags from 'striptags';

function highlight (text, successCondition, failureCondition) {
    var highlighted = text;
    highlighted = highlighted.replace(successCondition, '<span class="highlight">'+successCondition+'</span>');
    highlighted = highlighted.replace(failureCondition, '<span class="highlight-danger">'+failureCondition+'</span>');
    return highlighted;
}

function createHtml(json, successCondition, failureCondition) {
    var html = '';
    var event = json;
    var details = event.details;
    var subject = event.subject !== null ? event.subject : '';
    var highlighted_subject = highlight(subject, successCondition, failureCondition);
    if ( null !== details ) {
		var striped_details = striptags(details, '<table><th><tr><td><p><b><span><a><br><blockguote>');
		var highlighted_details = highlight (striped_details, successCondition, failureCondition );
        if (event.type === 'text')
            html = '<b>' + highlighted_subject + '</b>' +'<br/>' + '<pre>' + highlighted_details + '</pre>'
        else {
            html = '<b>' + highlighted_subject + '</b>' +'<br/>' + highlighted_details;
        }
    }
    return html;
}

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
	var view = View();
	view.btnStatusSelectionClick();
	view.btnResetFilterClick();
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
    
	$(document).on('click','.js-showDetails' ,function (e) {
		e.preventDefault();
		var url = e.currentTarget.dataset.url;
		var title = e.currentTarget.dataset.title;
        var successCondition = e.currentTarget.dataset.successcondition;
        var failureCondition = e.currentTarget.dataset.failurecondition;
        $.ajax({
			url: url,
			type: 'GET',
			dataType: 'json',
			success: function (json) {
				var html = createHtml(json, successCondition, failureCondition);
				import('sweetalert2').then((Swal) => {
					Swal.default.fire({
                        title: '',
						html: html
					});
				});
			}
		});
	});

    $(document).on('click','.close' ,function (e) {
		var number = e.target.id.slice(2);
		$('#ov'+number).fadeOut(300);
	    $('.bootstrap-table').show();
	});
	$('.overlay').hide();
});