let registros = [];

function registrarAsistencia() {
    const input = document.getElementById('rfidInput').value.trim();
    if (input === "") {
        alert("Por favor ingrese su nombre o código.");
        return;
    }

    const fecha = new Date();
    const registro = {
        nombre: input,
        fecha: fecha.toLocaleDateString(),
        hora: fecha.toLocaleTimeString()
    };

    registros.push(registro);
    agregarFilaTabla(registro);

    document.getElementById('rfidInput').value = "";

    const notificacion = document.getElementById('notification');
    notificacion.style.display = 'block';
    setTimeout(() => {
        notificacion.style.display = 'none';
    }, 2000);
}

function agregarFilaTabla(registro) {
    const tabla = document.getElementById('tablaAsistencia').getElementsByTagName('tbody')[0];
    const nuevaFila = tabla.insertRow();

    const celdaNombre = nuevaFila.insertCell(0);
    const celdaFecha = nuevaFila.insertCell(1);
    const celdaHora = nuevaFila.insertCell(2);

    celdaNombre.textContent = registro.nombre;
    celdaFecha.textContent = registro.fecha;
    celdaHora.textContent = registro.hora;
}

function descargarReporte() {
    if (registros.length === 0) {
        alert("No hay asistencias registradas aún.");
        return;
    }

    const contenido = registros.map(r => `Nombre/Código: ${r.nombre} | Fecha: ${r.fecha} | Hora: ${r.hora}`).join('\n') + '\n';
    const blob = new Blob([contenido], { type: "text/plain" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "reporte_asistencia.txt";
    a.click();

    URL.revokeObjectURL(url);
}
