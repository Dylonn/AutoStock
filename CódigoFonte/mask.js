document.addEventListener('DOMContentLoaded', function() {
    // Inicializa o Inputmask para CPF e CNPJ
    Inputmask({
        mask: [
            '999.999.999-99', // Máscara de CPF
            '99.999.999/9999-99' // Máscara de CNPJ
        ],
        keepStatic: true // Permite que a máscara se ajuste ao tipo de entrada
    }).mask('#cpfcnpj');
});
