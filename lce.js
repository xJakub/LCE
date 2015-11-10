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

        if (true || !oldLink || confirm("¿Quieres editar el enlace? Si eliges cancelar, serás redirigido al vídeo.")) {

            var newLink = prompt("Escribe el enlace del vídeo para " + $(this).text(), oldLink);

            if (newLink != null && newLink != oldLink) {
                $(this).parent().children('.editInput').val(newLink);
                $(this).parent().submit();

            }

            return false;
        }

    });
});