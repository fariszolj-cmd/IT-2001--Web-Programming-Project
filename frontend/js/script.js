// some scripts

// jquery ready start
$(document).ready(function() {
	// jQuery code


    /* ///////////////////////////////////////

    THESE FOLLOWING SCRIPTS ONLY FOR BASIC USAGE, 
    For sliders, interactions and other

    */ ///////////////////////////////////////
    

	//////////////////////// Prevent closing from click inside dropdown
    $(document).on('click', '.dropdown-menu', function (e) {
      e.stopPropagation();
    });


    $('.js-check :radio').change(function () {
        var check_attr_name = $(this).attr('name');
        if ($(this).is(':checked')) {
            $('input[name='+ check_attr_name +']').closest('.js-check').removeClass('active');
            $(this).closest('.js-check').addClass('active');
           // item.find('.radio').find('span').text('Add');

        } else {
            item.removeClass('active');
            // item.find('.radio').find('span').text('Unselect');
        }
    });


    $('.js-check :checkbox').change(function () {
        var check_attr_name = $(this).attr('name');
        if ($(this).is(':checked')) {
            $(this).closest('.js-check').addClass('active');
           // item.find('.radio').find('span').text('Add');
        } else {
            $(this).closest('.js-check').removeClass('active');
            // item.find('.radio').find('span').text('Unselect');
        }
    });



	//////////////////////// Bootstrap tooltip
	if($('[data-toggle="tooltip"]').length>0) {  // check if element exists
		$('[data-toggle="tooltip"]').tooltip()
	} // end if




    
}); 
// jquery end


$(document).ready(function () {

    function showSection(id) {
    $(".spa-section").hide();

    if (id === "home") {
        $("#home").show();
        $("#store").show();
    } else if (id === "store") {
        $("#store").show();
    } else if (id === "about" || id === "contact") {
        $("#" + id).show();
    } else {
        $("#home").show();
        $("#store").show();
    }
}

    $(".spa-link").on("click", function (event) {
        event.preventDefault();

        const id = $(this).attr("href").substring(1);

        if (!document.getElementById(id)) return;

        history.pushState(null, "", "#" + id);
        showSection(id);
    });

    window.addEventListener("popstate", function () {
        showSection(location.hash.substring(1) || "home");
    });

    showSection(location.hash.substring(1) || "home");

    $("#contact-form").on("submit", function (event) {
        event.preventDefault();
        alert("Message submitted!");
    });

});
