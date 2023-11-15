// Función para mostrar las alertas de validadores
function showAlert(element, message, color, type=false){
    let auxElement;
    let auxSmall;

    if(type == "chosen") {
        auxElement = element.parent().find('.chosen-container');
        auxElement.find(".chosen-single").css('border-color', color);
        auxElement.css('margin-bottom', 0);
    } else {
        element.css('border-color', color);
    }

    if(type == "form-group") {
        element.parent().parent().find('label').css('color', color);
        element.parent().css('margin-bottom', 0);
        auxSmall = element.parent().parent().find('small');
        auxSmall.removeAttr('hidden');
        auxSmall.css('color', color);
        auxSmall.html(message);
    } else {
        element.parent().find('label').css('color', color);
        element.parent().find('small').removeAttr('hidden');
        element.parent().find('small').css('color', color);
        element.parent().find('small').html(message);
    }

} //showAlert

// Función para validar los datos del formulario
function validateForm() {
    let checkRequired, checkPasswords, checkEmail, checkNumbers;

    // Se comprueban los campos obligatorios
    checkRequired = checkRequiredFields();

    // Se comprueba que las dos contraseñas coincidan
    if( $("#password1").val() != $("#password2").val() ) {
        $(".passwordInput").each(function() {
            showAlert($(this), 'Las contraseñas no coinciden', 'red');
        });
        checkPasswords = false;
    } else {
        $(".passwordInput").each(function() {
            showAlert($(this), 'Correcto', 'green');
        });
        checkPasswords = true;
    }

    // Validación del correo electrónico
    checkEmail = validateEmail( $("#email").val() ); 

    if(checkEmail) {
        showAlert($("#email"), 'Correcto', 'green');
    } else {
        showAlert($("#email"), 'Compruebe su email', 'red');
    }

    // Se comprueba que los campos numéricos solo contengan números
    checkNumbers = checkNumericFields();

    // Se comprueba que todas las validaciones hayan ido bien
    if ( checkRequired && checkPasswords && checkEmail && checkNumbers ) {
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
                showAlert($(this), 'Debe seleccionar una opción', 'red', "chosen");
            } else {
                showAlert($(this), 'Este campo no puede estar vacío', 'red');
            }
            auxResult = false;
        } else {
            if( $(this).is("select") ) {
                showAlert($(this), 'Correcto', 'green', "chosen");
            } else {
                showAlert($(this), 'Correcto', 'green');
            }
        }
    });

    return auxResult;
} // checkRequiredFields

// Comprobar campos numéricos
function checkNumericFields() {
    let auxResult = true;

    // Teléfonos, minutos bolsa y frecuencia de pago de transporte
    $(".naturalNumber").each(function() {
        if( $(this).val() != "" && !validateNumber($(this).val(), true) ) {
            showAlert($(this), 'Introduce un valor numérico', 'red', ($(this).attr("id") == "transportPaymentFrequency" ) ? "form-group" : false);
            auxResult = false;
        } else {
            showAlert($(this), 'Correcto', 'green', ($(this).attr("id") == "transportPaymentFrequency" ) ? "form-group" : false);
        }
    });

    // Campos numéricos de la parte de facturación
    $(".numericField").each(function() {
        if( $(this).val() != "" && !$.isNumeric( $(this).val() ) ) {
            showAlert($(this), 'Introduce un valor numérico', 'red', "form-group");
            auxResult = false;
        } else {
            showAlert($(this), 'Correcto', 'green', "form-group");
        }
    });

    return auxResult;
}

// Validación simple de email
function validateEmail(email) {
    let re = /\S+@\S+\.\S+/;
    return re.test(email);
}