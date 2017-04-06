jQuery(document).ready(function($) { 
  $("#sortable,#sortable-toolbar").sortable({ 
    opacity: 0.7,
    distance: 10, 
    helper: "clone", 
    forcePlaceholderSize:true,
    update: function(event,ui) {
      var newOrder = $(this).sortable('toArray').toString();
        $.post("options.php",{order: newOrder});
	$('#order').val(newOrder);
    },
  });
  
  $("#sortable,#sortable-toolbar").disableSelection();
});

 