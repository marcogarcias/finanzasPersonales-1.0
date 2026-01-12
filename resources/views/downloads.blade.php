@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Descarga Masiva XML</h1>
        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full border">Conexión Segura (FIEL)</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 h-full">
        
        <!-- Sidebar / Formulario -->
        <aside class="w-full lg:w-1/3 bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-fit">
            <h2 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">Nueva Solicitud</h2>
            
            <form id="downloadForm" class="space-y-4">
                <!-- Autenticación (Pestañas) -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <input type="hidden" name="auth_mode" id="authMode" value="ciec">
                    
                    <!-- Tabs Header -->
                    <div class="flex border-b border-gray-100 bg-gray-50/50">
                        <button type="button" onclick="switchTab('ciec')" id="tab-ciec"
                            class="flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-blue-600 text-blue-600 bg-white">
                            Contraseña (CIEC)
                        </button>
                        <button type="button" onclick="switchTab('fiel')" id="tab-fiel"
                            class="flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-gray-400 hover:text-gray-600">
                            e.firma (FIEL)
                        </button>
                    </div>

                    <div class="p-4 space-y-3">
                        <!-- CIEC Form -->
                        <div id="form-ciec" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-600">RFC</label>
                                <input type="text" name="rfc" id="rfcInput" placeholder="RFC del contribuyente"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 border text-sm uppercase">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-600">Contraseña (CIEC)</label>
                                <input type="password" name="password_ciec" id="passCiec" placeholder="••••••••"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 border text-sm">
                            </div>
                        </div>

                        <!-- FIEL Form -->
                        <div id="form-fiel" class="hidden space-y-3">
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-600">Certificado (.cer)</label>
                                <input type="file" name="certificate" id="certFile" accept=".cer"
                                    class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-600">Llave Privada (.key)</label>
                                <input type="file" name="private_key" id="keyFile" accept=".key"
                                    class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1 text-gray-600">Contraseña e.firma</label>
                                <input type="password" name="password_fiel" id="passFiel" placeholder="••••••••"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 border text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rango de Fechas -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>    
                        Periodo de Consulta
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1 text-gray-600">Inicio</label>
                            <input type="date" name="start_date" required value="{{ date('Y-m-d', strtotime('-1 week')) }}"
                                class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1 text-gray-600">Fin</label>
                            <input type="date" name="end_date" required value="{{ date('Y-m-d') }}"
                                class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 transition">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-medium mb-1 text-gray-600">Tipo de Comprobante</label>
                        <select name="download_type" class="w-full rounded-md border-gray-300 p-2 border text-sm focus:ring focus:ring-blue-100 bg-white transition">
                            <option value="recibidos">Facturas Recibidas (Gastos)</option>
                            <option value="emitidos">Facturas Emitidas (Ingresos)</option>
                        </select>
                    </div>
                </div>

                <!-- Carpeta de Destino -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Ubicación de Descarga
                    </h3>
                    
                    <div class="flex gap-2">
                        <input type="text" name="custom_path" id="customPathInput" readonly
                            value="{{ getenv('USERPROFILE') . '\Documents' }}"
                            class="w-full rounded-md border-gray-300 p-2 border text-xs text-gray-500 bg-gray-100 focus:ring-0 cursor-not-allowed">
                        
                        <button type="button" id="btnChangeFolder"
                            class="px-3 py-2 bg-white border border-gray-300 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-50 shadow-sm transition">
                            ...
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Se creará una subcarpeta con el RFC.</p>
                </div>

                <button type="submit" id="btnSubmit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex justify-center items-center gap-2 mt-2">
                    <span>Iniciar Descarga</span>
                    <svg id="spinner" class="animate-spin h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>
        </aside>

        <!-- Main Content / Tabla -->
        <section class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Cola de Procesamiento</h2>
                <span class="text-xs text-gray-400">Actualización en tiempo real</span>
            </div>

            <div class="overflow-x-auto flex-1 p-0">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Periodo</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">Progreso</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="downloadsTableBody">
                        <!-- Las filas se agregarán aquí dinámicamente -->
                        <tr id="emptyRow">
                            <td colspan="5" class="px-5 py-12 border-b border-gray-200 bg-white text-sm text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <span>No hay descargas activas en esta sesión.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Modal para Captcha -->
    <div id="captchaModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden transform transition-all animate-bounce-short">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Resolución de Captcha</h3>
                </div>
                
                <p class="text-sm text-gray-500 mb-6">El SAT solicita verificación. Por favor ingresa los caracteres que ves en la imagen:</p>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-6 flex justify-center">
                    <img id="captchaImage" src="" alt="Captcha SAT" class="h-12 object-contain rounded shadow-sm bg-white p-1">
                </div>

                <input type="hidden" id="captchaJobId">
                <input type="text" id="captchaAnswer" 
                    placeholder="Escribe el código aquí"
                    class="w-full text-center text-xl font-bold tracking-[0.5em] uppercase p-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all mb-4">
                
                <button type="button" onclick="submitCaptchaAnswer()" id="btnSubmitCaptcha"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2">
                    <span>Continuar Descarga</span>
                    <svg id="captchaSpinner" class="animate-spin h-4 w-4 text-white hidden" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('downloadForm');
    const tableBody = document.getElementById('downloadsTableBody');
    const emptyRow = document.getElementById('emptyRow');
    const btnSubmit = document.getElementById('btnSubmit');
    const spinner = document.getElementById('spinner');
    const btnChangeFolder = document.getElementById('btnChangeFolder');
    const customPathInput = document.getElementById('customPathInput');

    // Selector de Carpeta (NativePHP)
    btnChangeFolder.addEventListener('click', async () => {
        try {
            // Cambiar texto temporalmente
            const originalText = btnChangeFolder.innerText;
            btnChangeFolder.innerText = '...';
            btnChangeFolder.disabled = true;

            const res = await fetch('{{ route("folder.select") }}');
            const data = await res.json();

            if (data.path) {
                // NativePHP devuelve rutas a veces con slashes mixtos, normalizamos a visual
                customPathInput.value = data.path; 
            } else if (data.error) {
                console.warn('Native dialog not available:', data.error);
                alert('No se pudo abrir el selector nativo. Verifica que estés ejecutando la app en modo escritorio.');
            }

        } catch (e) {
            console.error(e);
        } finally {
            btnChangeFolder.innerText = '...'; // Icono original
            btnChangeFolder.disabled = false;
        }
    });

    // Función para cambiar entre pestañas CIEC y FIEL
    window.switchTab = function(mode) {
        const authMode = document.getElementById('authMode');
        const tabCiec = document.getElementById('tab-ciec');
        const tabFiel = document.getElementById('tab-fiel');
        const formCiec = document.getElementById('form-ciec');
        const formFiel = document.getElementById('form-fiel');
        
        authMode.value = mode;
        
        if (mode === 'ciec') {
            tabCiec.className = 'flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-blue-600 text-blue-600 bg-white';
            tabFiel.className = 'flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-gray-400 hover:text-gray-600';
            formCiec.classList.remove('hidden');
            formFiel.classList.add('hidden');
        } else {
            tabFiel.className = 'flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-blue-600 text-blue-600 bg-white';
            tabCiec.className = 'flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 border-transparent text-gray-400 hover:text-gray-600';
            formFiel.classList.remove('hidden');
            formCiec.classList.add('hidden');
        }
    }

    // Manejo del formulario
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validación manual rápida según el modo
        const mode = document.getElementById('authMode').value;
        if (mode === 'ciec') {
            if (!document.getElementById('rfcInput').value || !document.getElementById('passCiec').value) {
                alert('Por favor completa el RFC y la Contraseña CIEC');
                return;
            }
        } else {
            if (!document.getElementById('certFile').files.length || !document.getElementById('keyFile').files.length || !document.getElementById('passFiel').value) {
                alert('Por favor selecciona el certificado, la llave y escribe la contraseña de la e.firma');
                return;
            }
        }

        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
        spinner.classList.remove('hidden');

        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("download.start") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                addDownloadRow(data.job_id, formData);
                startPolling(data.job_id);
                // No reseteamos todo el form para no perder la ruta de descarga, pero sí los passwords
                document.getElementById('passCiec').value = '';
                document.getElementById('passFiel').value = '';
            } else {
                alert('Error al iniciar descarga: ' + (data.message || JSON.stringify(data)));
            }

        } catch (error) {
            console.error(error);
            alert('Ocurrió un error de red o de servidor. Verifica tu conexión.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
            spinner.classList.add('hidden');
        }
    });

    function addDownloadRow(jobId, formData) {
        if(emptyRow) emptyRow.remove();

        const startDate = formData.get('start_date');
        const endDate = formData.get('end_date');
        const type = formData.get('download_type');
        const mode = formData.get('auth_mode');
        const rowId = `row-${jobId}`;

        const html = `
            <tr id="${rowId}" class="hover:bg-gray-50 transition border-l-4 border-transparent hover:border-blue-500">
                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                    <div class="flex items-center">
                        <div class="ml-1">
                            <p class="text-gray-900 font-semibold whitespace-no-wrap text-xs">Ahora</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm font-mono text-xs text-gray-600">
                    ${startDate} <span class="text-gray-300">➜</span> ${endDate}
                </td>
                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 uppercase tracking-wide">
                        ${type}
                    </span>
                    <span class="text-[9px] block text-gray-400 mt-1 uppercase">${mode}</span>
                </td>
                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden shadow-inner">
                        <div id="progress-${jobId}" class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out" style="width: 2%"></div>
                    </div>
                    <p id="msg-${jobId}" class="text-[10px] text-gray-400 mt-1 truncate max-w-[200px]">Iniciando ${mode}...</p>
                </td>
                <td class="px-5 py-4 border-b border-gray-100 bg-white text-sm">
                    <span id="badge-${jobId}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>
                        En Cola
                    </span>
                </td>
            </tr>
        `;

        tableBody.insertAdjacentHTML('afterbegin', html);
    }

    function startPolling(jobId) {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(`/download/status/${jobId}`);
                const statusData = await res.json();

                updateRow(jobId, statusData);

                if (statusData.status === 'completed' || statusData.status === 'failed') {
                    clearInterval(interval);
                }
            } catch (e) {
                console.error('Polling error', e);
            }
        }, 2000); 
    }

    function updateRow(jobId, data) {
        const progressBar = document.getElementById(`progress-${jobId}`);
        const msgLabel = document.getElementById(`msg-${jobId}`);
        const badge = document.getElementById(`badge-${jobId}`);

        if (progressBar) progressBar.style.width = `${data.progress}%`;
        if (msgLabel) msgLabel.textContent = data.message;
        
        // Manejo especial de Captcha
        if (data.status === 'awaiting_captcha') {
            const modal = document.getElementById('captchaModal');
            const captchaImg = document.getElementById('captchaImage');
            const jobIdInput = document.getElementById('captchaJobId');
            
            // Si el modal está oculto, lo mostramos con los datos del job
            if (modal.classList.contains('hidden')) {
                captchaImg.src = data.captcha_url; // Base64 directo
                jobIdInput.value = jobId;
                modal.classList.remove('hidden');
                document.getElementById('captchaAnswer').value = '';
                document.getElementById('captchaAnswer').focus();
            }
        }

        if (badge) {
            let colorClass = 'bg-yellow-100 text-yellow-800';
            let dotColor = 'bg-yellow-500';
            let ping = true;
            let label = 'Procesando';

            if (data.status === 'completed') {
                colorClass = 'bg-green-100 text-green-800';
                dotColor = 'bg-green-500';
                ping = false;
                label = 'Completado';
            } else if (data.status === 'failed') {
                colorClass = 'bg-red-100 text-red-800';
                dotColor = 'bg-red-500';
                ping = false;
                label = 'Error';
            } else if (data.status === 'awaiting_captcha') {
                colorClass = 'bg-orange-100 text-orange-800';
                dotColor = 'bg-orange-500';
                ping = true;
                label = 'Esperando Captcha';
            }

            badge.className = `inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}`;
            badge.innerHTML = `
                <span class="relative flex h-2 w-2">
                  ${ping ? `<span class="animate-ping absolute inline-flex h-full w-full rounded-full ${dotColor} opacity-75"></span>` : ''}
                  <span class="relative inline-flex rounded-full h-2 w-2 ${dotColor}"></span>
                </span>
                ${label}
            `;
        }
    }

    async function submitCaptchaAnswer() {
        const jobId = document.getElementById('captchaJobId').value;
        const answer = document.getElementById('captchaAnswer').value;
        const btn = document.getElementById('btnSubmitCaptcha');
        const spinner = document.getElementById('captchaSpinner');
        const modal = document.getElementById('captchaModal');

        if (!answer) return;

        btn.disabled = true;
        spinner.classList.remove('hidden');

        try {
            const response = await fetch('{{ route("captcha.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    job_id: jobId,
                    captcha_answer: answer
                })
            });

            const data = await response.json();
            if (data.success) {
                modal.classList.add('hidden');
            } else {
                alert('Error al enviar captcha');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión al enviar captcha');
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
        }
    }

    // Permitir enviar con Enter
    document.getElementById('captchaAnswer')?.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') submitCaptchaAnswer();
    });
</script>
@endpush
