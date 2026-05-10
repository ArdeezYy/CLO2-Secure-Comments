(function () {
    'use strict';

    var eyeIcon = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    var eyeOffIcon = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.576 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"></path><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"></path><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"></path><path d="m2 2 20 20"></path></svg>';

    function setupPasswordToggles() {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            var inputId = button.getAttribute('data-password-toggle');
            var input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            button.innerHTML = eyeIcon;

            button.addEventListener('click', function () {
                var isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                button.innerHTML = isHidden ? eyeOffIcon : eyeIcon;
                button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                input.focus();
            });
        });
    }

    function setRuleState(rule, isValid) {
        if (!rule) {
            return;
        }

        rule.classList.toggle('is-valid', isValid);
    }

    function setupSignupPasswordPolicy() {
        var form = document.querySelector('[data-password-policy]');

        if (!form) {
            return;
        }

        var password = form.querySelector('[data-password-input]');
        var confirmPassword = form.querySelector('[data-confirm-password-input]');
        var submit = form.querySelector('[data-signup-submit]');
        var rules = {
            length: form.querySelector('[data-rule="length"]'),
            lower: form.querySelector('[data-rule="lower"]'),
            upper: form.querySelector('[data-rule="upper"]'),
            number: form.querySelector('[data-rule="number"]'),
            symbol: form.querySelector('[data-rule="symbol"]'),
            match: form.querySelector('[data-rule="match"]')
        };

        if (!password || !confirmPassword || !submit) {
            return;
        }

        function validate() {
            var value = password.value;
            var confirmValue = confirmPassword.value;
            var state = {
                length: value.length >= 8,
                lower: /[a-z]/.test(value),
                upper: /[A-Z]/.test(value),
                number: /[0-9]/.test(value),
                symbol: /[^A-Za-z0-9]/.test(value),
                match: value !== '' && value === confirmValue
            };
            var isValid = state.length && state.lower && state.upper && state.number && state.symbol && state.match;

            Object.keys(state).forEach(function (key) {
                setRuleState(rules[key], state[key]);
            });

            submit.disabled = !isValid;
            submit.setAttribute('aria-disabled', isValid ? 'false' : 'true');

            return isValid;
        }

        submit.disabled = true;
        submit.setAttribute('aria-disabled', 'true');
        password.addEventListener('input', validate);
        confirmPassword.addEventListener('input', validate);

        form.addEventListener('submit', function (event) {
            if (!validate()) {
                event.preventDefault();
            }
        });

        validate();
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupPasswordToggles();
        setupSignupPasswordPolicy();
    });
}());
