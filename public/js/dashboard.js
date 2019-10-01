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