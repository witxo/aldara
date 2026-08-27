@if(config('recaptcha.enabled') && config('recaptcha.site_key'))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
    <script>
        window.recaptchaExecute = function(action) {
            if (typeof grecaptcha !== 'undefined' && grecaptcha.execute) {
                return grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: action});
            }
            return Promise.resolve('');
        };
    </script>
@endif