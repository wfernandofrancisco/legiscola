{{--
  Progresso de upload via XHR (vídeo/material).
  O form precisa ter a classe js-ajax-upload-form.
  O painel fica no próprio form (várias aulas na mesma página) e sobe na tela durante o envio.
--}}
<div class="js-ajax-upload-progress hidden fixed inset-x-4 bottom-4 z-50 mx-auto w-[min(100%,32rem)] rounded-2xl border border-indigo-200 bg-white p-4 shadow-2xl shadow-indigo-950/20 dark:border-indigo-800 dark:bg-slate-900"
    role="status" aria-live="polite">
    <div class="mb-2 flex items-center justify-between gap-3 text-sm">
        <span class="js-ajax-upload-label font-medium text-indigo-900 dark:text-indigo-100">Enviando arquivo…</span>
        <span class="js-ajax-upload-pct tabular-nums font-semibold text-indigo-700 dark:text-indigo-300">0%</span>
    </div>
    <div class="h-2.5 overflow-hidden rounded-full bg-indigo-100 dark:bg-indigo-900/80">
        <div class="js-ajax-upload-bar h-full rounded-full bg-indigo-600 transition-[width] duration-150 ease-out dark:bg-indigo-400" style="width: 0%"></div>
    </div>
    <p class="js-ajax-upload-hint mt-2 text-xs text-indigo-800/80 dark:text-indigo-200/80">
        Não feche nem atualize esta página até terminar.
    </p>
    <p class="js-ajax-upload-error mt-2 hidden text-sm text-red-600 dark:text-red-400"></p>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                var MAX_VIDEO_BYTES = 200 * 1024 * 1024;
                var uploading = false;

                function formatBytes(n) {
                    if (n < 1024 * 1024) return (n / 1024).toFixed(0) + ' KB';
                    return (n / (1024 * 1024)).toFixed(1) + ' MB';
                }

                function boxOf(form) {
                    return form.querySelector('.js-ajax-upload-progress');
                }

                function setProgress(form, pct, label, hint) {
                    var box = boxOf(form);
                    if (!box) return;
                    var bar = box.querySelector('.js-ajax-upload-bar');
                    var pctEl = box.querySelector('.js-ajax-upload-pct');
                    var labelEl = box.querySelector('.js-ajax-upload-label');
                    var hintEl = box.querySelector('.js-ajax-upload-hint');
                    if (bar) bar.style.width = pct + '%';
                    if (pctEl) pctEl.textContent = pct + '%';
                    if (label && labelEl) labelEl.textContent = label;
                    if (hint && hintEl) hintEl.textContent = hint;
                }

                function showError(form, msg) {
                    var box = boxOf(form);
                    var err = box ? box.querySelector('.js-ajax-upload-error') : null;
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

                window.addEventListener('beforeunload', function (e) {
                    if (!uploading) return;
                    e.preventDefault();
                    e.returnValue = '';
                });

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

                        var progress = boxOf(form);
                        var err = progress ? progress.querySelector('.js-ajax-upload-error') : null;
                        var submitBtn = form.querySelector('button[type="submit"]');

                        if (err) {
                            err.classList.add('hidden');
                            err.textContent = '';
                        }
                        clearFieldErrors(form);

                        if (hasVideo && videoInput.files[0].size > MAX_VIDEO_BYTES) {
                            showError(form, 'O vídeo passa de 200 MB. Comprima com HandBrake (Fast 720p30) ou use o link do YouTube.');
                            return;
                        }

                        if (progress) {
                            progress.classList.remove('hidden');
                        }

                        setProgress(form, 0, 'Enviando arquivo…', 'Não feche nem atualize esta página até terminar.');

                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                        }

                        uploading = true;

                        var fd = new FormData(form);
                        var xhr = new XMLHttpRequest();
                        xhr.open(form.getAttribute('method') || 'POST', form.action, true);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.upload.addEventListener('progress', function (ev) {
                            if (!ev.lengthComputable) return;
                            var pct = Math.max(1, Math.min(99, Math.round((ev.loaded / ev.total) * 100)));
                            setProgress(
                                form,
                                pct,
                                'Enviando arquivo… ' + formatBytes(ev.loaded) + ' de ' + formatBytes(ev.total),
                                'Não feche nem atualize esta página até terminar.'
                            );
                        });

                        xhr.upload.addEventListener('load', function () {
                            setProgress(form, 100, 'Arquivo enviado. Salvando no servidor…', 'Quase pronto, aguarde a confirmação.');
                        });

                        xhr.addEventListener('load', function () {
                            uploading = false;
                            var payload = null;
                            try {
                                payload = JSON.parse(xhr.responseText || '{}');
                            } catch (ignore) {
                                payload = null;
                            }

                            if (xhr.status >= 200 && xhr.status < 300 && payload && payload.redirect) {
                                setProgress(form, 100, 'Concluído!', 'Atualizando a página…');
                                window.location.href = payload.redirect;
                                return;
                            }

                            if (xhr.status === 422 && payload && payload.errors) {
                                showFieldErrors(form, payload.errors);
                                showError(form, payload.message || 'Revise os campos destacados e tente de novo.');
                                setProgress(form, 0, 'Envio interrompido', 'Corrija os erros e salve novamente.');
                            } else {
                                showError(form, 'Não foi possível salvar. Tente de novo ou use um arquivo menor.');
                                setProgress(form, 0, 'Falha no envio', 'Verifique a conexão e o tamanho do arquivo.');
                            }

                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                            }
                        });

                        xhr.addEventListener('error', function () {
                            uploading = false;
                            showError(form, 'Falha de rede durante o upload. Tente novamente.');
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
