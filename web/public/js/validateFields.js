// Función para mostrar las alertas de validadores
function showAlert(element, message, color){

    element.parent().find('label').css('color', color);
    element.css('border-color', color);
    element.next().removeAttr('hidden');
    element.parent().find('small').css('color', color);
    element.parent().find('small').html(message);
} //showAlert

// Función para validar los datos del formulario
function validateForm() {
    let checkRequired, checkEmail, checkNumbers;

    // Se comprueban los campos obligatorios
    checkRequired = checkRequiredFields();

    // Validación del correo electrónico
    checkEmail = validateEmail( $("#email").val() );

    if(checkEmail) {
        showAlert($("#email"), 'Correcto', 'green');
    } else {
        showAlert($("#email"), 'Compruebe su email', 'red');
    }

    // Se comprueba que los campos numéricos solo contengan números
    if ($('#selector-banderas').val() == -1) {
        showAlert($("#selector-banderas"), 'Seleccione un prefijo', 'red');
    } else{
        showAlert($("#selector-banderas"), 'Correcto', 'green');
    }

    if (checkPhone($('#phone').val())) {
        checkNumbers = checkNumericFields($('#selector-banderas').val(), $('#phone').val());
        
        if(checkNumbers) {
            showAlert($("#phone"), 'Correcto', 'green');
        } else {
            showAlert($("#phone"), 'Compruebe su número con el prefijo', 'red');
        }
    } else{
        showAlert($("#phone"), 'Introduzca un numero correcto', 'red');
    }
    

    // Se comprueba que todas las validaciones hayan ido bien
    if ( checkRequired && checkEmail && checkNumbers ) {
        return true;
    } else {
        return false;
    }
}

// Comprobar campos obligatorios
function checkRequiredFields() {
    let auxResult = true;

    $(".requiredField").each(function() {
        if( $(this).val().trim() == "" ) {
            if( $(this).is("select") ) {
                showAlert($(this), 'Debe seleccionar una opción', 'red');
            } else {
                showAlert($(this), 'Este campo no puede estar vacío', 'red');
            }
            auxResult = false;
        } else {
            if( $(this).is("select") ) {
                showAlert($(this), 'Correcto', 'green');
            } else {
                showAlert($(this), 'Correcto', 'green');
            }
        }
    });

    return auxResult;
} // checkRequiredFields

// Comprobar campos numéricos
function checkNumericFields(prefijo, numero) {
    let auxResult = true;

    // Campos numéricos de la parte de facturación
    let regex = /^\+\d{1,4}\d{1,14}$/;
    auxResult = regex.test(prefijo + numero);

    return auxResult;
}

function checkPhone(numero) {
    let auxResult = true;

    // Campos numéricos de la parte de facturación
    let regex = /^\d{1,14}$/;
    auxResult = regex.test(numero);

    return auxResult;
}

// Validación simple de email
function validateEmail(email) {
    let re = /\S+@\S+\.\S+/;
    return re.test(email);
}

$('.btn-success').click(function(){

    validateForm();
});