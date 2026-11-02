// Máscara de moeda: "123456" vira "1.234,56" durante a digitação.
document.querySelectorAll('[data-mascara="moeda"]').forEach((campo) => {
    campo.addEventListener('input', () => {
        const digitos = campo.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');

        if (digitos === '') {
            campo.value = '';
            return;
        }

        const centavos = digitos.padStart(3, '0');
        const inteiro = centavos.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        campo.value = `${inteiro},${centavos.slice(-2)}`;
    });
});
