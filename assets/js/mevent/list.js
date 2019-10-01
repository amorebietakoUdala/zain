import '../../css/event/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import 'tableexport.jquery.plugin/tableExport';
import 'jquery-ui';
import URI from 'urijs';

// SweetAlert2 is imported dynamically
// https://github.com/babel/babel/issues/10140
// Until it's fixed, this import is necesary.
// import Swal from 'sweetalert2';

$(document).ready(function(){
	console.log('Berria');
	$('#taula').bootstrapTable({
		cache : false,
		showExport: false,
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
		    exportDataType: $(this).val()
		});
	    });
	});
	
	$(document).on('click','.js-ezabatu_botoia' ,function (e) {
	    var locale = $('html').attr('lang');
	    e.preventDefault();
	    var url = $(e.currentTarget).data('url');
		import('sweetalert2').then((Swal) => {
			Swal.default.fire({
			title: locale === 'eu' ? 'Ezabatu?' : 'Borrar?',
			text: locale === 'eu' ? 'Konfirmatu mesedez' : 'Confirme por favor',
			confirmButtonText: locale === 'eu' ? 'Bai' : 'Sí',
			cancelButtonText: locale === 'eu' ? 'Ez' : 'No',
			showCancelButton: true,
			showLoaderOnConfirm: true,
			preConfirm: function () {
				var table = $('#taula');
				if ( ( typeof table.bootstrapTable ) != 'undefined' ) {
				var options = table.bootstrapTable('getOptions');
				var pageNumber = options.pageNumber;
				uri = new URI(url);
				var uri = new URI(url);
				uri.addQuery("returnPage",pageNumber);
				url = uri.toString();
				}
				window.location.href=url;
			}
			});
		});
		
	});
});