/**
 * masks.js — Máscaras de input reutilizáveis + ViaCEP
 *
 * Uso: adicione o atributo data-mask no <input>
 *   data-mask="cnpj"   → 00.000.000/0000-00
 *   data-mask="cpf"    → 000.000.000-00
 *   data-mask="phone"  → (00) 0000-0000 ou (00) 00000-0000 (detecta cel/fixo)
 *   data-mask="cep"    → 00000-000  +  busca ViaCEP ao sair do campo
 *
 * O servidor recebe somente dígitos (a máscara é removida no submit).
 * API pública: window.AppMasks.init()  — reinicializa valores existentes
 *              window.AppMasks.apply(input) — aplica máscara em um input
 *              window.AppMasks.digits(v)    — retorna só dígitos de uma string
 */
(function () {
    'use strict';

    // ─── Helpers ────────────────────────────────────────────────────

    function digits(v) {
        return String(v || '').replace(/\D/g, '');
    }

    // ─── Funções de máscara ─────────────────────────────────────────

    function maskCnpj(v) {
        v = digits(v).slice(0, 14);
        if (v.length <=  2) return v;
        if (v.length <=  5) return v.slice(0, 2) + '.' + v.slice(2);
        if (v.length <=  8) return v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5);
        if (v.length <= 12) return v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8);
        return v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8, 12) + '-' + v.slice(12);
    }

    function maskCpf(v) {
        v = digits(v).slice(0, 11);
        if (v.length <=  3) return v;
        if (v.length <=  6) return v.slice(0, 3) + '.' + v.slice(3);
        if (v.length <=  9) return v.slice(0, 3) + '.' + v.slice(3, 6) + '.' + v.slice(6);
        return v.slice(0, 3) + '.' + v.slice(3, 6) + '.' + v.slice(6, 9) + '-' + v.slice(9);
    }

    function maskPhone(v) {
        v = digits(v).slice(0, 11);
        if (v.length === 0)  return '';
        if (v.length <= 2)   return '(' + v;
        if (v.length <= 6)   return '(' + v.slice(0, 2) + ') ' + v.slice(2);
        // Fixo: (00) 0000-0000  — 10 dígitos
        if (v.length <= 10)  return '(' + v.slice(0, 2) + ') ' + v.slice(2, 6) + '-' + v.slice(6);
        // Celular: (00) 00000-0000 — 11 dígitos
        return '(' + v.slice(0, 2) + ') ' + v.slice(2, 7) + '-' + v.slice(7);
    }

    function maskCep(v) {
        v = digits(v).slice(0, 8);
        if (v.length <= 5) return v;
        return v.slice(0, 5) + '-' + v.slice(5);
    }

    function applyMask(input) {
        var type   = input.dataset.mask;
        var masked;
        switch (type) {
            case 'cnpj':  masked = maskCnpj(input.value);  break;
            case 'cpf':   masked = maskCpf(input.value);   break;
            case 'phone': masked = maskPhone(input.value); break;
            case 'cep':   masked = maskCep(input.value);   break;
            default: return;
        }
        if (input.value !== masked) input.value = masked;
    }

    // ─── ViaCEP ─────────────────────────────────────────────────────

    // Mapeamento campo ViaCEP → name do input no form
    var CEP_MAP = {
        logradouro: ['logradouro'],
        bairro: ['bairro'],
        localidade: ['cidade'],
        uf: ['uf', 'estado'],
        complemento: ['complemento'],
    };

    function fillAddress(form, data) {
        Object.keys(CEP_MAP).forEach(function (key) {
            if (!data[key]) return;
            CEP_MAP[key].forEach(function (fieldName) {
                var el = form.querySelector('[name="' + fieldName + '"]');
                if (el && !el.readOnly && !el.disabled) {
                    el.value = data[key];
                }
            });
        });
    }

    function lookupCep(cepInput) {
        var cep = digits(cepInput.value);
        if (cep.length !== 8) return;

        var form = cepInput.closest('form');
        if (!form) return;

        // Feedback visual: opacidade reduzida enquanto busca
        cepInput.style.opacity = '0.5';
        cepInput.readOnly = true;

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.erro) fillAddress(form, data);
            })
            .catch(function () {
                // Falha silenciosa — usuário preenche manualmente
            })
            .finally(function () {
                cepInput.style.opacity = '';
                cepInput.readOnly = false;
            });
    }

    // ─── Event delegation (funciona mesmo após re-render AJAX) ───────

    // Aplica máscara enquanto digita
    document.addEventListener('input', function (e) {
        if (e.target.dataset && e.target.dataset.mask) {
            applyMask(e.target);
        }
    });

    // Busca CEP ao sair do campo (blur não borbulha → capture: true)
    document.addEventListener('blur', function (e) {
        if (e.target.dataset && e.target.dataset.mask === 'cep') {
            lookupCep(e.target);
        }
    }, true);

    // Remove máscara antes de enviar ao servidor (somente dígitos)
    document.addEventListener('submit', function (e) {
        e.target.querySelectorAll('[data-mask]').forEach(function (input) {
            input.value = digits(input.value);
        });
    }, true);

    // ─── Init: aplica máscara nos valores já preenchidos (edição) ────

    function init() {
        document.querySelectorAll('[data-mask]').forEach(function (input) {
            if (input.value) applyMask(input);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ─── API pública ─────────────────────────────────────────────────
    window.AppMasks = {
        init:   init,
        apply:  applyMask,
        digits: digits,
    };

}());
