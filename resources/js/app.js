import {
    startAuthentication,
    startRegistration,
    browserSupportsWebAuthn,
} from '@simplewebauthn/browser';
import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('authForm', () => ({
        mode: 'login',
        username: '',
        name: '',
        browserSupported: browserSupportsWebAuthn(),
        error: null,
        submit() {
            this.error = null;

            if (this.mode === 'login') {
                return this.submitLogin();
            }

            return this.submitRegister();
        },
        submitRegister() {
            window.axios
                .post('/registration/options', {
                    username: this.username,
                })
                .then((response) => startRegistration(response.data))
                .then((attResp) => axios.post('/registration/verify', attResp))
                .then((verificationResponse) => {
                    if (verificationResponse.data?.verified) {
                        return window.location.reload();
                    }

                    this.error =
                        'Something went wrong verifying the registration.';
                })
                .catch((error) => {
                    this.error = error?.response?.data?.message || error;
                });
        },
        submitLogin() {
            window.axios
                .post('/authentication/options', {
                    username: this.username,
                })
                .then((response) => startAuthentication(response.data))
                .then((attResp) =>
                    axios.post('/authentication/verify', attResp),
                )
                .then((verificationResponse) => {
                    if (verificationResponse.data?.verified) {
                        return window.location.reload();
                    }

                    this.error =
                        'Something went wrong verifying the authentication.';
                })
                .catch((error) => {
                    const errorMessage =
                        error?.response?.data?.message || error;

                    if (errorMessage === 'User not found') {
                        this.mode = 'confirmRegistration';
                        return;
                    }

                    this.error = error?.response?.data?.message || error;
                });
        },
    }));
});
