/**
 * Created by Jakub on 02/11/2015.
 */
$(function() {
    $('.editableResult').click(function() {
        $('.editResult').hide();
        $('.editableResult').show();
        $(this).hide();
        $(this).parent().children('.editResult').show();
    });

    $('.editResult select').change(function() {
        $(this).parent().submit();
    });


    $('.editableVideo').click(function() {
        var oldLink = $(this).parent().children('.editInput').val();

        var newLink = prompt("Escribe el enlace del vídeo para " + $(this).text(), oldLink);

        if (newLink != null && newLink != oldLink) {
            $(this).parent().children('.editInput').val(newLink);
            $(this).parent().submit();

        }

        return false;
    });

    $('#selectNumber').change(function() {
        var number = $(this).val();
        $('.playerEdit').hide();
        $('.playerEdit.player'+number).show();
    });

    $('#navSeason').change(function() {
       location.href = '/' + $(this).val() + '/';
    });
});

function showApplicationVote(obj) {
    $(obj).closest('.application').find('.applicationvote').show();
    $(obj).hide();
}

function removeFriendlyVideo(obj, videoid) {
    if (confirm('¿Seguro que quieres quitar este vídeo de la página?')) {
        var form = $(obj).closest('form');
        var input = form.find('input[name="removeid"]');
        input.val(videoid);
        form.submit();
    }
}