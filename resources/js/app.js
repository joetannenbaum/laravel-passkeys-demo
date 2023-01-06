import {
    startAuthentication,
    startRegistration,
    browserSupportsWebAuthn,
} from '@simplewebauthn/browser';
import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('authForm', () => ({
        mode: 'register',
        username: '',
        name: '',
        browserSupported: browserSupportsWebAuthn(),
        error: null,
        submit() {
            this.error = null;

            if (this.mode === 'register') {
                return this.submitRegister();
            }

            return this.submitLogin();
        },
        submitRegister() {
            window.axios
                .post('/registration/options', {
                    username: this.username,
                })
                // Pass the options to the authenticator and wait for a response
                .then((response) => startRegistration(response.data))
                .then((attResp) => axios.post('/registration/verify', attResp))
                .then((verificationResponse) => {
                    if (
                        verificationResponse.data &&
                        verificationResponse.data.verified
                    ) {
                        console.log('success!');
                    } else {
                        console.log('invalid?', verificationResponse.data);
                    }
                })
                .catch((error) => {
                    this.error = error;
                });
        },
        submitLogin() {
            window.axios
                .post('/authentication/options', {
                    username: this.username,
                })
                // Pass the options to the authenticator and wait for a response
                .then((response) => startAuthentication(response.data))
                .then((attResp) =>
                    axios.post('/authentication/verify', attResp),
                )
                .then((verificationResponse) => {
                    if (
                        verificationResponse.data &&
                        verificationResponse.data.verified
                    ) {
                        console.log('success!');
                    } else {
                        console.log('invalid?', verificationResponse.data);
                    }
                })
                .catch((error) => {
                    this.error = error;
                });
        },
    }));
});
