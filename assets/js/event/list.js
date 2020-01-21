import '../../css/event/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import 'tableexport.jquery.plugin/tableExport';
import 'jquery-ui';
import striptags from 'striptags';

// Dynamically imported
// import Swal from 'sweetalert2';

function createHtml(json) {
    var html = '';
    var event = json;
    var details = event.details;
    if ( null !== details ) {
        if (event.type === 'text')
            html = '<pre>' + event.details + '</pre>'
        else {
            html = striptags(details, '<table><th><tr><td><p><b><span><a><br><blockguote>');
        }
    }
    return html;
}

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
	$(document).on('click','.js-showDetails' ,function (e) {
		e.preventDefault();
		var url = e.currentTarget.dataset.url;
		var title = e.currentTarget.dataset.title;
		$.ajax({
			url: url,
			type: 'GET',
			dataType: 'json',
			success: function (json) {
				var html = createHtml(json);
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
    $(document).on('click','.overlay' ,function (e) {
        e.currentTarget.hide();
    });
	var $table = $('#taula');
	$(function () {
	    $('#toolbar').find('select').change(function () {
		$table.bootstrapTable('destroy').bootstrapTable({
		    exportDataType: $(this).val(),
		});
	    });
	});
});