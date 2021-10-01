(function($) {
	
	$(document).ready(function() {

        // Give all field instructions bootstrap styles
        $('body.page-template-form-page .af-field-instructions').addClass('alert alert-info');
        
        // Toggle field instructions
        // $('.af-field.has-instructions label').click(function(e){
        $('.why-button').click(function(e){
            console.log('cc');
            e.preventDefault();
            $(this).parent().parent().children('.af-field-instructions').slideToggle(250);
        });


        // SUBMIT APPLICATION
        if ($('button.modal-confirm-submit').length) {
            //initSubmitApplication();
        }

    });

    function initSubmitApplication() {
        // on load, change button type to 'button'
        $submitBtn = $('button.modal-confirm-submit');
        toggleSubmitButtonType($submitBtn);
        $modal = $($submitBtn.data('target'));

        $modal
            .on('show.bs.modal', function(e) {
                toggleSubmitButtonType($submitBtn);
            })
            .on('hide.bs.modal', function(e) {
                toggleSubmitButtonType($submitBtn);
            });

        // on modal, change button type to 'submit'

        // on modal close
        /*
        $('#form_5e39b93ada6ac .af-submit-button').on('click', 'preventDefaultFormSubmission')
        // $('body').on('click', '#attempt-submit-application', preventDefaultFormSubmission);

        $('#confirm-submit-application').click(function(){
            // console.log('click');
            $('body')
                .off('click', '#attempt-submit-application', preventDefaultFormSubmission)
                .find('#attempt-submit-application')
                .trigger('click');
            //$('#form_5e39b93ada6ac').submit();
        });

        // $('#attempt-submit-application').trigger('click');
        */
    }

    function toggleSubmitButtonType($btn) {
        if ($btn.attr('type') == 'submit' ) {
            $btn.attr('type', 'button');
        } else {
            $btn.attr('type', 'submit');
        }
        console.log('change button');
    }

    function preventDefaultFormSubmission(e) {
        console.log('toggle preventing submission');
        e.preventDefault();
    }


	
})( jQuery );