document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('prestamoForm');
    const socioSelect = document.getElementById('socio_id');
    const montoInput = document.getElementById('monto');
    const tasaInput = document.getElementById('tasa_interes');
    const plazoInput = document.getElementById('plazo_meses');
    const fechaSolicitudInput = document.getElementById('fecha_solicitud');
    const cuotaMensualSpan = document.getElementById('cuota_mensual');
    const fechaInicioSpan = document.getElementById('fecha_inicio');
    const fechaFinSpan = document.getElementById('fecha_fin');
    const fechaInicioInput = document.getElementById('fecha_inicio_input');
    const fechaFinInput = document.getElementById('fecha_fin_input');

    function calcularPrestamo() {
        const monto = parseFloat(montoInput.value) || 0;
        const tasaAnual = parseFloat(tasaInput.value) || 0;
        const plazo = parseInt(plazoInput.value) || 0;
        const fechaSolicitud = fechaSolicitudInput.value;
        const selectedOption = socioSelect.options[socioSelect.selectedIndex];
        const saldo6Meses = parseFloat(selectedOption.getAttribute('data-saldo-6-meses')) || 0;
        const maxPrestamo = parseFloat(selectedOption.getAttribute('data-max-prestamo')) || 0;

        if (monto && tasaAnual && plazo && fechaSolicitud) {
            if (monto > maxPrestamo) {
                alert('El monto solicitado no puede exceder el doble del saldo a 6 meses ($' + maxPrestamo.toFixed(2) + ').');
                montoInput.value = '';
                cuotaMensualSpan.textContent = '0.00';
                return;
            }

            const tasaMensual = tasaAnual / 100 / 12;
            const cuotaMensual = (monto * tasaMensual * Math.pow(1 + tasaMensual, plazo)) / (Math.pow(1 + tasaMensual, plazo) - 1);
            cuotaMensualSpan.textContent = cuotaMensual.toFixed(2);

            const fechaInicio = new Date(fechaSolicitud);
            const fechaInicioStr = fechaInicio.toISOString().split('T')[0];
            fechaInicioSpan.textContent = fechaInicioStr;
            fechaInicioInput.value = fechaInicioStr;

            const fechaFin = new Date(fechaInicio);
            fechaFin.setMonth(fechaFin.getMonth() + plazo);
            const fechaFinStr = fechaFin.toISOString().split('T')[0];
            fechaFinSpan.textContent = fechaFinStr;
            fechaFinInput.value = fechaFinStr;
        } else {
            cuotaMensualSpan.textContent = '0.00';
            fechaInicioSpan.textContent = '-';
            fechaFinSpan.textContent = '-';
            fechaInicioInput.value = '';
            fechaFinInput.value = '';
        }
    }

    montoInput.addEventListener('input', calcularPrestamo);
    tasaInput.addEventListener('input', calcularPrestamo);
    plazoInput.addEventListener('input', calcularPrestamo);
    fechaSolicitudInput.addEventListener('input', calcularPrestamo);
    socioSelect.addEventListener('change', calcularPrestamo);

    calcularPrestamo();

    // Depuración
    form.addEventListener('submit', (e) => {
        console.log('Enviando:', {
            socio_id: socioSelect.value,
            monto: montoInput.value,
            tasa_interes: tasaInput.value,
            plazo_meses: plazoInput.value,
            fecha_solicitud: fechaSolicitudInput.value,
            fecha_inicio: fechaInicioInput.value,
            fecha_fin: fechaFinInput.value
        });
    });
});