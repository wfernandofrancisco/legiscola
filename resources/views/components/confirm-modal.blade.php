<div id="confirm-modal" style="display: none;" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-6 max-w-sm mx-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-full mx-auto mb-4 bg-red-100 dark:bg-red-900">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <h3 id="modal-title" class="text-center font-bold text-lg text-gray-900 dark:text-white"></h3>
        <p id="modal-message" class="text-center text-gray-600 dark:text-gray-400 mt-2 text-sm leading-relaxed"></p>

        <div class="flex gap-3 mt-6">
            <button onclick="document.getElementById('confirm-modal').style.display = 'none'"
                class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-900 font-medium transition">
                Cancelar
            </button>
            <button id="modal-confirm" onclick="confirmAction()"
                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
    let modalFormId = null;

    function showConfirmModal(title, message, formId) {
        modalFormId = formId;
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-message').textContent = message;
        document.getElementById('confirm-modal').style.display = 'flex';
    }

    function confirmAction() {
        if (modalFormId) {
            document.getElementById(modalFormId).submit();
        }
        document.getElementById('confirm-modal').style.display = 'none';
    }

    document.getElementById('confirm-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
</script>
