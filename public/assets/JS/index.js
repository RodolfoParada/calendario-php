

document.addEventListener('DOMContentLoaded', function() {

    // Referencias
    const dateInput = document.getElementById('date-input-hidden');
    const weekDisplayTitle = document.getElementById('week-display-title');
    const daysOfWeek = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    // Variable basePath pasada desde PHP
    const basePath = '<?= htmlspecialchars($basePath) ?>'; 

    // =========================================================
    // FUNCIONES AUXILIARES
    // =========================================================

    function getMondayOfWeek(dateObj) {
        const d = new Date(dateObj);
        const day = d.getDay(); 
        const diff = (day === 0 ? -6 : 1 - day); 
        d.setDate(d.getDate() + diff);
        return d;
    }

    function updateCalendarHeaders(refDate) {
        const monday = getMondayOfWeek(refDate);
        const headerRow = document.getElementById('day-number-row');

        let html = "";
        let endOfWeek = new Date(monday);

        for (let i = 0; i < 7; i++) {
            const d = new Date(monday);
            d.setDate(monday.getDate() + i);
            if (i === 6) endOfWeek = d; 

            html += `
                <th data-day-index="${i}">
                    ${daysOfWeek[i]}<br>
                    <span class="day-number">${d.getDate()}</span>
                </th>
            `;
        }

        headerRow.innerHTML = html;

        // Actualizar el título de la semana
        const opt = { day: "numeric", month: "short" };
        const inicio = monday.toLocaleDateString("es-ES", opt);
        const fin = endOfWeek.toLocaleDateString("es-ES", opt);

        weekDisplayTitle.textContent = `${inicio} - ${fin} ${monday.getFullYear()}`;

        loadVisits(monday);
    }


    // =========================================================
    // FLATPICKR: Inicialización
    // =========================================================

    const today = new Date();

    const fp = flatpickr(dateInput, {
        defaultDate: today,
        dateFormat: "d M Y",
        enableTime: false,
        locale: "es",
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                updateCalendarHeaders(selectedDates[0]);
            }
        }
    });

    // Carga inicial de la semana
    updateCalendarHeaders(today);


    // =========================================================
    // FLECHAS → SEMANA ANTERIOR / SEMANA SIGUIENTE
    // =========================================================

    document.getElementById('prev-week').addEventListener('click', () => {
        let current = fp.selectedDates[0] ?? new Date();
        let newDate = new Date(current);
        newDate.setDate(current.getDate() - 7); 
        fp.setDate(newDate, true); 
    });

    document.getElementById('next-week').addEventListener('click', () => {
        let current = fp.selectedDates[0] ?? new Date();
        let newDate = new Date(current);
        newDate.setDate(current.getDate() + 7); 
        fp.setDate(newDate, true);
    });

    // Función "agendar()" dummy para evitar errores si se presiona el botón
    window.agendar = function() {
        // En un proyecto real, aquí se usaría Fetch API para enviar los datos a PHP
        const tecnico = document.getElementById('tecnico').value;
        const cliente = document.getElementById('cliente').value;
        const fecha = document.getElementById('fecha').value;
        const hora = document.getElementById('hora').value;
        
        console.log(`Agendando: Téc: ${tecnico}, Cliente: ${cliente}, Fecha: ${fecha}, Hora: ${hora}`);
        alert("¡Visita agendada! (Simulado)");
    };

});