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
        showAlert($("#email"), 'Correct', 'green');
    } else {
        showAlert($("#email"), 'Check your email', 'red');
    }

    checkNumbers = checkPhone($('#phone').val());
        
    if(checkNumbers) {
        showAlert($("#phone2"), 'Correct', 'green');
        showAlert($("#phone"), 'Correct', 'green');
    } else {
        showAlert($("#phone2"), 'Check your phone number', 'red');
        showAlert($("#phone"), 'Check your phone number', 'red');
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
                showAlert($(this), 'You must select an option', 'red');
            } else {
                showAlert($(this), 'This field can not be blank', 'red');
            }
            auxResult = false;
        } else {
            if( $(this).is("select") ) {
                showAlert($(this), 'Correct', 'green');
            } else {
                showAlert($(this), 'Correct', 'green');
            }
        }
    });

    return auxResult;
} // checkRequiredFields

function checkPhone(numero) {
    let auxResult = true;

    // Campos numéricos de la parte de facturación
    let regex = /^[0-9-()+]{3,20}/;
    auxResult = regex.test(numero);

    return auxResult;
}

// Validación simple de email
function validateEmail(email) {
    let re = /\S+@\S+\.\S+/;
    return re.test(email);
}

$('.btn-success').click(function(){

    if (validateForm()) {
        sendForm();
    }
});

const input = document.querySelector("#phone");
const iti   = window.intlTelInput(input, {
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
    preferredCountries: ["es"],
    separateDialCode: true
});

$('#phone').change(function(){
    $('#phone2').val($(this).val());
});

function getData() {
    let data = {
        'firstName'     :      $('#firstName').val(),
        'lastName'      :      $('#lastName').val(),
        'email'         :      $('#email').val(),
        'phone'         :      iti.getNumber(),
        'affair'        :      $('#affair').val(),
        'description'   :      $('#description').val()
    };
    
    return data;
}