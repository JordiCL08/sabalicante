<?php
////////////////////MENSAJES O ERRORES//////////////////////////////////

if (isset($_SESSION['errores']) && is_array($_SESSION['errores']) && count($_SESSION['errores']) > 0) {
    echo '<ul class="alert alert-danger mensajes-internos">';
    foreach ($_SESSION['errores'] as $error) {
        echo "<li>{$error}</li>";
    }
    echo '</ul>';
    unset($_SESSION['errores']);
}

if (isset($_SESSION['mensaje'])) {
    echo '<div class="alert alert-success mensajes-internos">';
    echo $_SESSION['mensaje'];
    echo '</div>';
    unset($_SESSION['mensaje']);
}


////////////////////////////////////////////////////////////////////


?>
<script>
    setTimeout(function() {
        var alertElements = document.querySelectorAll('.mensajes-internos');
        alertElements.forEach(function(alertElement) {
            alertElement.classList.remove('show');
            alertElement.classList.add('fade');
        });
    }, 3000);
</script>