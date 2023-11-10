$(document).ready(function() {
    $('.btn-process').click(function(e) {
        e.preventDefault();

        let row = $(this).parents('tr');

        let id = row.data('id');

        let form = $('#form-update');

        let url = form.attr('action').replace(':TASK_ID', id);

        let data = form.serialize();

        $('#button-' + id).addClass('disabled');
        $.post(url, data, function(result) {
            $('#button-' + id).removeClass('disabled');
            if (result.processed == 1) {
                // $('#message-warning').addClass("hidden");

                // $('#message').removeClass('hidden');

                $('#glyphicon-' + id).removeClass('glyphicon-time text-danger').addClass('glyphicon-ok text-success');
                $('#glyphicon-' + id).prop('title', 'Finish');

                // $('#user-message').html('The task has been finished.');
                alert('The task has been finished.');
            } else{
                // $('#message').addClass('hidden');
                // $('#message-warning').removeClass("hidden");
                // $('#user-message-warning').html('The task was already finished.');
                alert('The task was already finished.');
            }
        }).fail(function() {
            $('#button-' + id).removeClass('disabled');
            alert('The task was not finished.')
        });
    });
});