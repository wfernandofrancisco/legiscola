{{--
  Progresso de upload via XHR (video/material).
  O form precisa ter a classe js-ajax-upload-form.
--}}
<div id="ajax-upload-progress" class="hidden mt-4 rounded-lg border border-indigo-200 bg-indigo-50/80 p-4 dark:border-indigo-800 dark:bg-indigo-950/40" role="status" aria-live="polite">
    <div class="mb-2 flex items-center justify-between gap-3 text-sm">
        <span id="ajax-upload-label" class="font-medium text-indigo-900 dark:text-indigo-100">Enviando arquivo…</span>
        <span id="ajax-upload-pct" class="tabular-nums font-semibold text-indigo-700 dark:text-indigo-300">0%</span>
    </div>
    <div class="h-2.5 overflow-hidden rounded-full bg-indigo-100 dark:bg-indigo-900/80">
        <div id="ajax-upload-bar" class="h-full rounded-full bg-indigo-600 transition-[width] duration-150 ease-out dark:bg-indigo-400" style="width: 0%"></div>
    </div>
    <p id="ajax-upload-hint" class="mt-2 text-xs text-indigo-800/80 dark:text-indigo-200/80">
        Não feche nem atualize esta página até terminar.
    </p>
    <p id="ajax-upload-error" class="mt-2 hidden text-sm text-red-600 dark:text-red-400"></p>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                var MAX_VIDEO_BYTES = 200 * 1024 * 1024;

                function $(id) {
                    return document.getElementById(id);
                }

                function formatBytes(n) {
                    if (n < 1024 * 1024) return (n / 1024).toFixed(0) + ' KB';
                    return (n / (1024 * 1024)).toFixed(1) + ' MB';
                }

                function setProgress(pct, label, hint) {
                    var bar = $('ajax-upload-bar');
                    var pctEl = $('ajax-upload-pct');
                    var labelEl = $('ajax-upload-label');
                    var hintEl = $('ajax-upload-hint');
                    if (bar) bar.style.width = pct + '%';
                    if (pctEl) pctEl.textContent = pct + '%';
                    if (label && labelEl) labelEl.textContent = label;
                    if (hint && hintEl) hintEl.textContent = hint;
                }

                function showError(msg) {
                    var box = $('ajax-upload-progress');
                    var err = $('ajax-upload-error');
                    if (box) box.classList.remove('hidden');
                    if (err) {
                        err.textContent = msg;
                        err.classList.remove('hidden');
                    }
                }

                function clearFieldErrors(form) {
                    form.querySelectorAll('[data-ajax-field-error]').forEach(function (el) {
                        el.remove();
                    });
                }

                function showFieldErrors(form, errors) {
                    clearFieldErrors(form);
                    Object.keys(errors || {}).forEach(function (field) {
                        var input = form.querySelector('[name="' + field + '"]');
                        if (!input) return;
                        var p = document.createElement('p');
                        p.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                        p.setAttribute('data-ajax-field-error', '1');
                        p.textContent = (errors[field] || [])[0] || 'Campo inválido.';
                        input.parentNode.appendChild(p);
                    });
                }

                document.querySelectorAll('form.js-ajax-upload-form').forEach(function (form) {
                    form.addEventListener('submit', function (e) {
                        var videoInput = form.querySelector('input[name="video_file"]');
                        var materialInput = form.querySelector('input[name="material_file"]');
                        var hasVideo = videoInput && videoInput.files && videoInput.files.length > 0;
                        var hasMaterial = materialInput && materialInput.files && materialInput.files.length > 0;

                        if (!hasVideo && !hasMaterial) {
                            return;
                        }

                        e.preventDefault();

                        var progress = $('ajax-upload-progress');
                        var err = $('ajax-upload-error');
                        var submitBtn = form.querySelector('button[type="submit"]');

                        if (err) {
                            err.classList.add('hidden');
                            err.textContent = '';
                        }
                        clearFieldErrors(form);

                        if (hasVideo && videoInput.files[0].size > MAX_VIDEO_BYTES) {
                            showError('O vídeo passa de 200 MB. Comprima com HandBrake (Fast 720p30) ou use o link do YouTube.');
                            return;
                        }

                        if (progress) {
                            progress.classList.remove('hidden');
                            progress.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }

                        setProgress(0, 'Enviando arquivo…', 'Não feche nem atualize esta página até terminar.');

                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                        }

                        var fd = new FormData(form);
                        var xhr = new XMLHttpRequest();
                        xhr.open(form.method || 'POST', form.action, true);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.upload.addEventListener('progress', function (ev) {
                            if (!ev.lengthComputable) return;
                            var pct = Math.max(1, Math.min(99, Math.round((ev.loaded / ev.total) * 100)));
                            setProgress(
                                pct,
                                'Enviando arquivo… ' + formatBytes(ev.loaded) + ' de ' + formatBytes(ev.total),
                                'Não feche nem atualize esta página até terminar.'
                            );
                        });

                        xhr.upload.addEventListener('load', function () {
                            setProgress(100, 'Arquivo enviado. Salvando no servidor…', 'Quase pronto, aguarde a confirmação.');
                        });

                        xhr.addEventListener('load', function () {
                            var payload = null;
                            try {
                                payload = JSON.parse(xhr.responseText || '{}');
                            } catch (ignore) {
                                payload = null;
                            }

                            if (xhr.status >= 200 && xhr.status < 300 && payload && payload.redirect) {
                                setProgress(100, 'Concluído!', 'Redirecionando…');
                                window.location.href = payload.redirect;
                                return;
                            }

                            if (xhr.status === 422 && payload && payload.errors) {
                                showFieldErrors(form, payload.errors);
                                showError(payload.message || 'Revise os campos destacados e tente de novo.');
                                setProgress(0, 'Envio interrompido', 'Corrija os erros e salve novamente.');
                            } else {
                                showError('Não foi possível salvar. Tente de novo ou use um arquivo menor.');
                                setProgress(0, 'Falha no envio', 'Verifique a conexão e o tamanho do arquivo.');
                            }

                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                            }
                        });

                        xhr.addEventListener('error', function () {
                            showError('Falha de rede durante o upload. Tente novamente.');
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                            }
                        });

                        xhr.send(fd);
                    });
                });
            })();
        </script>
    @endpush
@endonce
