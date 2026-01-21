@extends('layouts.app')

@section('content')
<div class="w-full h-full flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <h1 class="text-2xl font-bold text-gray-800">Explorador de Bóveda</h1>
        <div class="flex gap-2">
            <input type="text" id="searchInput" placeholder="Filtrar en resultados..." 
                class="px-4 py-2 border rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 w-64">
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 flex-1 overflow-hidden">
        
        <!-- Sidebar / Filtros -->
        <aside class="w-full lg:w-1/3 bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Filtros de Bóveda</h2>
            
            <form id="bovedaForm" class="space-y-5 flex-1">
                <div class="p-4 bg-blue-50/50 rounded-lg border border-blue-100">
                    <!-- 1. RFC Select -->
                    <div class="mt-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contribuyente (RFC)</label>
                        <select id="rfcSelect" class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white">
                            <option value="">Cargando RFCs...</option>
                        </select>
                    </div>

                    <!-- 2. Tipo Select -->
                    <div class="mt-3">
                        <label class="block text-xs font-medium mb-1 text-gray-700">Clase (Ingresos/Egresos)</label>
                        <select id="typeSelect" disabled class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white disabled:bg-gray-100">
                            <option value="">Seleccione Clase...</option>
                        </select>
                    </div>

                    <!-- 3. Año Select -->
                    <div class="mt-3">
                        <label class="block text-xs font-medium mb-1 text-gray-700">Año Fiscal</label>
                        <select id="yearSelect" disabled class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white disabled:bg-gray-100">
                            <option value="">Seleccione Año...</option>
                        </select>
                    </div>

                    <!-- 4. Meses Checkboxes -->
                    <div id="monthsContainer" class="hidden mt-3">
                        <label class="block text-xs font-medium mb-2 text-gray-700">Meses con Datos</label>
                        <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-md border border-gray-200 max-h-40 overflow-y-auto" id="monthsList">
                            <!-- Checkboxes via JS -->
                            <span class="text-xs text-gray-400 col-span-2 text-center">Seleccione un año primero</span>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Botón Exportar -->
            <div class="mt-6 pt-4 border-t border-gray-100">
                <button id="btnExportExcel" disabled
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white rounded-lg font-bold text-sm hover:bg-green-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path></svg>
                    EXPORTAR A EXCEL
                </button>
            </div>
        </aside>

        <!-- Main Content / Tabla -->
         <section class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center shrink-0">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Comprobantes en Base de Datos</h2>
                <div class="flex items-center gap-4">
                    <button id="btnCheckStatus" class="hidden flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        VERIFICAR ESTATUS (<span id="checkCount">0</span>)
                    </button>
                    <button id="btnDeleteSelected" class="hidden flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        ELIMINAR (<span id="selectedCount">0</span>)
                    </button>
                    <span class="text-xs text-gray-400">Encontrados: <span id="fileCount" class="font-bold text-gray-800">0</span></span>
                </div>
            </div>

            <div class="overflow-auto flex-1 p-0 relative">
                <!-- Loader -->
                <div id="tableLoader" class="hidden absolute inset-0 bg-white/80 z-10 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>

                <table class="min-w-full leading-normal">
                    <thead class="sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-left w-10">
                                <input type="checkbox" id="selectAll" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer">
                            </th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-left text-[10px] font-bold text-gray-600 uppercase">UUID / Folio</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-left text-[10px] font-bold text-gray-600 uppercase">Emisor</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-left text-[10px] font-bold text-gray-600 uppercase">Fecha</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-left text-[10px] font-bold text-gray-600 uppercase">Tipo</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-center text-[10px] font-bold text-gray-600 uppercase">Estatus SAT</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-right text-[10px] font-bold text-gray-600 uppercase">Total</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-100 text-center text-[10px] font-bold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="filesTableBody">
                        <tr>
                            <td colspan="8" class="px-5 py-20 text-center text-gray-400 text-sm italic">
                                Use los filtros laterales para listar los comprobantes fiscales.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-10 right-10 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-white border-l-4 border-green-500 shadow-2xl rounded-lg p-4 flex items-center space-x-4 min-w-[300px] border">
            <div id="toastIconContainer" class="bg-green-100 p-2 rounded-full text-green-600">
                <svg id="toastIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div>
                <h4 id="toastTitle" class="font-bold text-gray-800 text-sm">Correcto</h4>
                <p id="toastMessage" class="text-xs text-gray-600 underline">Exportación exitosa</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- Referencias DOM ---
    const rfcSelect = document.getElementById('rfcSelect');
    const typeSelect = document.getElementById('typeSelect');
    const yearSelect = document.getElementById('yearSelect');
    const monthsContainer = document.getElementById('monthsContainer');
    const monthsList = document.getElementById('monthsList');
    const tableBody = document.getElementById('filesTableBody');
    const tableLoader = document.getElementById('tableLoader');
    const fileCountLabel = document.getElementById('fileCount');
    const btnExportExcel = document.getElementById('btnExportExcel');
    
    // Elementos de selección masiva
    const selectAllCheckbox = document.getElementById('selectAll');
    const btnDeleteSelected = document.getElementById('btnDeleteSelected');
    const selectedCountLabel = document.getElementById('selectedCount');
    const btnCheckStatus = document.getElementById('btnCheckStatus');
    const checkCountLabel = document.getElementById('checkCount');

    // --- Inicialización ---
    loadRfcs();

    // --- Event Listeners ---

    // Selección masiva
    selectAllCheckbox.addEventListener('change', () => {
        const checkboxes = document.querySelectorAll('.file-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
        updateSelectedUI();
    });

    tableBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('file-checkbox')) {
            updateSelectedUI();
        }
    });

    btnDeleteSelected.addEventListener('click', bulkDeleteFiles);
    btnCheckStatus.addEventListener('click', bulkCheckStatus);

    // 1. Select RFC -> Cargar Tipos (Clases)
    rfcSelect.addEventListener('change', () => {
        const rfc = rfcSelect.value;
        resetFilters('type'); 
        if(rfc) loadTypes(rfc);
    });

    // 2. Select Clase -> Cargar Años
    typeSelect.addEventListener('change', () => {
        const type = typeSelect.value;
        resetFilters('year');
        if(type) loadYears(rfcSelect.value, type);
    });

    // 3. Select Año -> Cargar Meses
    yearSelect.addEventListener('change', () => {
        const year = yearSelect.value;
        resetFilters('month');
        if(year) loadMonths(rfcSelect.value, typeSelect.value, year);
    });

    // 4. Click Exportar -> Diálogo y generación
    btnExportExcel.addEventListener('click', async () => {
        const rfc = rfcSelect.value;
        const type = typeSelect.value;
        const year = yearSelect.value;
        const checkedMonths = Array.from(document.querySelectorAll('input[name="months[]"]:checked')).map(cb => cb.value);

        if (!rfc || !type || !year || checkedMonths.length === 0) return;

        const originalHTML = btnExportExcel.innerHTML;
        btnExportExcel.disabled = true;
        btnExportExcel.innerHTML = `
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            PROCESANDO...
        `;

        let qs = `rfc=${rfc}&type=${type}&year=${year}`;
        checkedMonths.forEach(m => qs += `&months[]=${m}`);

        try {
            const res = await fetch(`{{ route('api.boveda.export') }}?` + qs);
            
            if (!res.ok) {
                const errorText = await res.text();
                console.error('Server error:', errorText);
                throw new Error('El servidor respondió con un error (Código ' + res.status + ').');
            }

            const data = await res.json();

            if(data.status === 'success') {
                showToast('Correcto', 'Exportación exitosa', 'success');
            } else if (data.message === 'Exportación cancelada') {
                // No hacer nada
            } else {
                showToast('Error', (data.error || 'No se pudo exportar el archivo'), 'error');
            }
        } catch (e) {
            console.error('Export error:', e);
            showToast('Error', 'Error de conexión: ' + e.message, 'error');
        } finally {
            btnExportExcel.disabled = false;
            btnExportExcel.innerHTML = originalHTML;
        }
    });

    // --- Funciones AJAX ---

    async function loadRfcs() {
        disableAll(true);
        const res = await fetch(`{{ route('api.boveda.scan') }}?level=rfc`);
        const data = await res.json();
        
        rfcSelect.innerHTML = '<option value="">Seleccione RFC...</option>';
        if(data.length === 0) {
            rfcSelect.innerHTML = '<option value="">Sin datos en BD</option>';
        }
        data.forEach(rfc => {
            rfcSelect.innerHTML += `<option value="${rfc}">${rfc}</option>`;
        });
        disableAll(false);
    }

    async function loadTypes(rfc) {
        const res = await fetch(`{{ route('api.boveda.scan') }}?level=type&rfc=${rfc}`);
        const data = await res.json();
        
        typeSelect.innerHTML = '<option value="">Seleccione Clase...</option>';
        data.forEach(type => {
             typeSelect.innerHTML += `<option value="${type}">${type.toUpperCase()}</option>`;
        });
        typeSelect.disabled = false;
    }

    async function loadYears(rfc, type) {
        const res = await fetch(`{{ route('api.boveda.scan') }}?level=year&rfc=${rfc}&type=${type}`);
        const data = await res.json();
        
        yearSelect.innerHTML = '<option value="">Seleccione Año...</option>';
        data.forEach(year => {
             yearSelect.innerHTML += `<option value="${year}">${year}</option>`;
        });
        yearSelect.disabled = false;
    }

    async function loadMonths(rfc, type, year) {
        const res = await fetch(`{{ route('api.boveda.scan') }}?level=month&rfc=${rfc}&type=${type}&year=${year}`);
        const data = await res.json();
        
        monthsList.innerHTML = '';
        if(data.length === 0) {
            monthsList.innerHTML = '<span class="text-xs text-red-400 col-span-2 text-center">Sin meses registrados</span>';
            return;
        }

        const monthNames = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        data.forEach(month => {
            const mInt = parseInt(month);
            const html = `
                <label class="flex items-center space-x-2 bg-white p-2 border rounded cursor-pointer hover:bg-blue-50 transition shadow-sm">
                    <input type="checkbox" name="months[]" value="${month}" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                    <span class="text-[10px] font-bold text-gray-700">${monthNames[mInt] || month}</span>
                </label>
            `;
            monthsList.insertAdjacentHTML('beforeend', html);
        });
        
        monthsContainer.classList.remove('hidden');
        
        // Listener para cambios en checkboxes
        monthsList.addEventListener('change', (e) => {
            if(e.target.name === 'months[]') {
                const checkedMonths = Array.from(document.querySelectorAll('input[name="months[]"]:checked')).map(cb => cb.value);
                if(checkedMonths.length > 0) {
                    loadFiles(rfcSelect.value, typeSelect.value, yearSelect.value, checkedMonths);
                } else {
                    clearTable();
                }
            }
        });
    }

    async function loadFiles(rfc, type, year, months) {
        tableLoader.classList.remove('hidden');
        tableBody.innerHTML = '';
        
        let qs = `rfc=${rfc}&type=${type}&year=${year}`;
        months.forEach(m => qs += `&months[]=${m}`);

        const res = await fetch(`{{ route('api.boveda.files') }}?` + qs);
        const files = await res.json();
        
        fileCountLabel.innerText = files.length;
        btnExportExcel.disabled = files.length === 0;

        if (files.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="px-5 py-20 text-center text-gray-400 italic">No se encontraron registros coincidentes.</td></tr>';
        } else {
            files.forEach(file => {
                const row = `
                    <tr class="hover:bg-blue-50/30 transition border-b border-gray-100">
                        <td class="px-5 py-3 text-left">
                            <input type="checkbox" value="${file.id}" class="file-checkbox rounded text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer">
                        </td>
                        <td class="px-5 py-3 text-xs">
                            <div class="font-bold text-gray-800">${file.folio || 'S/F'}</div>
                            <div class="text-[10px] text-gray-400 font-mono">${file.uuid}</div>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600 uppercase">
                           ${file.emisor}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                             ${file.fecha}
                        </td>
                        <td class="px-5 py-3 text-xs text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${file.tipo === 'I' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'}">
                                ${file.tipo}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shadow-sm border ${getStatusStyles(file.estado)}">
                                ${file.estado ? file.estado.toUpperCase() : 'DESCONOCIDO'}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-right font-bold text-gray-900">
                            ${file.total} <span class="text-[9px] text-gray-400 ml-1">${file.moneda}</span>
                        </td>
                        <td class="px-5 py-3 text-center flex justify-center gap-1">
                            <button title="Abrir XML" class="p-1.5 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </button>
                            <button onclick="deleteFile(${file.id})" title="Eliminar Registro" class="p-1.5 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        }
        
        selectAllCheckbox.checked = false;
        updateSelectedUI();
        tableLoader.classList.add('hidden');
    }

    // --- Utils ---
    function getStatusStyles(estado) {
        if (!estado) return 'bg-gray-100 text-gray-600 border-gray-200';
        
        switch (estado.toLowerCase()) {
            case 'vigente':
                return 'bg-green-50 text-green-700 border-green-200';
            case 'cancelado':
                return 'bg-red-50 text-red-700 border-red-200';
            case 'no encontrado':
                return 'bg-amber-50 text-amber-700 border-amber-200';
            default:
                return 'bg-blue-50 text-blue-700 border-blue-200';
        }
    }

    function resetFilters(level) {
        if(level === 'rfc') {
            rfcSelect.value = '';
        }
        if(level === 'rfc' || level === 'type') {
            typeSelect.innerHTML = '<option value="">Seleccione Clase...</option>';
            typeSelect.disabled = true;
        }
        if(level === 'rfc' || level === 'type' || level === 'year') {
            yearSelect.innerHTML = '<option value="">Seleccione Año...</option>';
            yearSelect.disabled = true;
        }
        if(level === 'rfc' || level === 'type' || level === 'year' || level === 'month') {
            monthsContainer.classList.add('hidden');
            monthsList.innerHTML = '';
            clearTable();
        }
    }

    function clearTable() {
        tableBody.innerHTML = '<tr><td colspan="7" class="px-5 py-20 text-center text-gray-400 italic">Use los filtros laterales para listar los comprobantes fiscales.</td></tr>';
        fileCountLabel.innerHTML = '0';
        btnExportExcel.disabled = true;
        selectAllCheckbox.checked = false;
        updateSelectedUI();
    }
    
    function disableAll(bool) {
       rfcSelect.disabled = bool;
    }

    async function deleteFile(id) {
        if (!confirm('¿Está seguro de eliminar este comprobante? Se eliminará el registro de la base de datos y el archivo físico XML.')) {
            return;
        }

        try {
            const res = await fetch(`{{ url('/api/boveda') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            if (res.ok) {
                showToast('Correcto', 'Comprobante eliminado con éxito', 'success');
                // Recargar lista actual
                const checkedMonths = Array.from(document.querySelectorAll('input[name="months[]"]:checked')).map(cb => cb.value);
                loadFiles(rfcSelect.value, typeSelect.value, yearSelect.value, checkedMonths);
            } else {
                showToast('Error', data.error || 'No se pudo eliminar el registro', 'error');
            }
        } catch (e) {
            console.error('Delete error:', e);
            showToast('Error', 'Error de conexión al eliminar', 'error');
        }
    }

    async function bulkCheckStatus() {
        const selectedCheckboxes = document.querySelectorAll('.file-checkbox:checked');
        const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (ids.length === 0) return;

        const originalHTML = btnCheckStatus.innerHTML;
        btnCheckStatus.disabled = true;
        btnCheckStatus.innerHTML = '<svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> VERIFICANDO...';

        try {
            const res = await fetch(`{{ route('api.boveda.bulkCheckStatus') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            });

            const data = await res.json();

            if (res.ok) {
                showToast('Correcto', data.message, 'success');
                // Recargar lista actual
                const checkedMonths = Array.from(document.querySelectorAll('input[name="months[]"]:checked')).map(cb => cb.value);
                loadFiles(rfcSelect.value, typeSelect.value, yearSelect.value, checkedMonths);
            } else {
                showToast('Error', data.error || 'No se pudo verificar el estatus', 'error');
            }
        } catch (e) {
            console.error('Bulk Check Status error:', e);
            showToast('Error', 'Error de conexión al verificar el estatus', 'error');
        } finally {
            btnCheckStatus.disabled = false;
            btnCheckStatus.innerHTML = originalHTML;
        }
    }

    async function bulkDeleteFiles() {
        const selectedCheckboxes = document.querySelectorAll('.file-checkbox:checked');
        const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (ids.length === 0) return;

        if (!confirm(`¿Está seguro de eliminar los ${ids.length} comprobantes seleccionados? Esta acción eliminará los registros de la base de datos y los archivos físicos XML.`)) {
            return;
        }

        const originalHTML = btnDeleteSelected.innerHTML;
        btnDeleteSelected.disabled = true;
        btnDeleteSelected.innerHTML = '<svg class="animate-spin h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ELIMINANDO...';

        try {
            const res = await fetch(`{{ route('api.boveda.bulkDestroy') }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            });

            const data = await res.json();

            if (res.ok) {
                showToast('Correcto', data.message, 'success');
                // Recargar lista actual
                const checkedMonths = Array.from(document.querySelectorAll('input[name="months[]"]:checked')).map(cb => cb.value);
                loadFiles(rfcSelect.value, typeSelect.value, yearSelect.value, checkedMonths);
            } else {
                showToast('Error', data.error || 'No se pudieron eliminar los registros', 'error');
            }
        } catch (e) {
            console.error('Bulk Delete error:', e);
            showToast('Error', 'Error de conexión al eliminar los registros', 'error');
        } finally {
            btnDeleteSelected.disabled = false;
            btnDeleteSelected.innerHTML = originalHTML;
        }
    }

    function updateSelectedUI() {
        const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
        selectedCountLabel.innerText = checkedCount;
        checkCountLabel.innerText = checkedCount;
        
        if (checkedCount > 0) {
            btnDeleteSelected.classList.remove('hidden');
            btnCheckStatus.classList.remove('hidden');
        } else {
            btnDeleteSelected.classList.add('hidden');
            btnCheckStatus.classList.add('hidden');
        }

        // Sincronizar selectAll
        const totalRows = document.querySelectorAll('.file-checkbox').length;
        selectAllCheckbox.checked = (checkedCount === totalRows && totalRows > 0);
    }

    function showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const tTitle = document.getElementById('toastTitle');
        const tMsg = document.getElementById('toastMessage');
        const tContainer = toast.querySelector('div');
        const tIconContainer = document.getElementById('toastIconContainer');
        const tIcon = document.getElementById('toastIcon');
        
        tTitle.innerText = title;
        tMsg.innerText = message;
        
        if (type === 'error') {
            tContainer.classList.replace('border-green-500', 'border-red-500');
            tIconContainer.classList.replace('bg-green-100', 'bg-red-100');
            tIconContainer.classList.replace('text-green-600', 'text-red-600');
            tIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
        } else {
            tContainer.classList.replace('border-red-500', 'border-green-500');
            tIconContainer.classList.replace('bg-red-100', 'bg-green-100');
            tIconContainer.classList.replace('text-red-600', 'text-green-600');
            tIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
        }

        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 4000);
    }
</script>
@endpush
@endsection
