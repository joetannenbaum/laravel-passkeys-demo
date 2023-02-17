<?php

namespace App\Http\Controllers;

use App\Auth\CredentialSourceRepository;
use App\Models\User;
use Cose\Algorithms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class RegistrationController extends Controller
{
    const CREDENTIAL_CREATION_OPTIONS_SESSION_KEY = 'publicKeyCredentialCreationOptions';

    public function generateOptions(Request $request)
    {
        $rpEntity = PublicKeyCredentialRpEntity::create(
            config('app.name'),
            parse_url(config('app.url'), PHP_URL_HOST),
            null,
        );

        $userEntity = PublicKeyCredentialUserEntity::create(
            $request->input('username'),
            $request->input('username'),
            $request->input('username'),
            null,
        );

        $challenge = random_bytes(16);

        $publicKeyCredentialParametersList = collect([
            Algorithms::COSE_ALGORITHM_ES256,
            Algorithms::COSE_ALGORITHM_ES256K,
            Algorithms::COSE_ALGORITHM_ES384,
            Algorithms::COSE_ALGORITHM_ES512,
            Algorithms::COSE_ALGORITHM_RS256,
            Algorithms::COSE_ALGORITHM_RS384,
            Algorithms::COSE_ALGORITHM_RS512,
            Algorithms::COSE_ALGORITHM_PS256,
            Algorithms::COSE_ALGORITHM_PS384,
            Algorithms::COSE_ALGORITHM_PS512,
            Algorithms::COSE_ALGORITHM_ED256,
            Algorithms::COSE_ALGORITHM_ED512,
        ])->map(
            fn ($algorithm) => PublicKeyCredentialParameters::create('public-key', $algorithm)
        )->toArray();

        $publicKeyCredentialCreationOptions =
            PublicKeyCredentialCreationOptions::create(
                $rpEntity,
                $userEntity,
                $challenge,
                $publicKeyCredentialParametersList,
            )
            ->setAttestation(
                PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE
            )
            ->setAuthenticatorSelection(
                AuthenticatorSelectionCriteria::create()
            )
            ->setExtensions(AuthenticationExtensionsClientInputs::createFromArray([
                'credProps' => true,
            ]));

        $serializedOptions = $publicKeyCredentialCreationOptions->jsonSerialize();

        if (!isset($serializedOptions['excludeCredentials'])) {
            // The JS side needs this, so let's set it up for success with an empty array
            $serializedOptions['excludeCredentials'] = [];
        }

        $serializedOptions['extensions'] = $serializedOptions['extensions']->jsonSerialize();

        $serializedOptions['authenticatorSelection']['residentKey'] = AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED;

        $request->session()->put(
            self::CREDENTIAL_CREATION_OPTIONS_SESSION_KEY,
            $serializedOptions
        );

        return $serializedOptions;
    }

    public function verify(Request $request, ServerRequestInterface $serverRequest)
    {
        $publicKeyCredentialSourceRepository = new CredentialSourceRepository();
        $tokenBindingHandler = IgnoreTokenBindingHandler::create();

        $attestationStatementSupportManager = AttestationStatementSupportManager::create();

        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        $extensionOutputCheckerHandler = ExtensionOutputCheckerHandler::create();

        $authenticatorAttestationResponseValidator = AuthenticatorAttestationResponseValidator::create(
            $attestationStatementSupportManager,
            $publicKeyCredentialSourceRepository,
            $tokenBindingHandler,
            $extensionOutputCheckerHandler,
        );

        $attestationObjectLoader = AttestationObjectLoader::create(
            $attestationStatementSupportManager
        );

        $publicKeyCredentialLoader = PublicKeyCredentialLoader::create(
            $attestationObjectLoader
        );

        $publicKeyCredential = $publicKeyCredentialLoader->load(json_encode($request->all()));

        $authenticatorAttestationResponse = $publicKeyCredential->getResponse();

        if (!$authenticatorAttestationResponse instanceof AuthenticatorAttestationResponse) {
            throw ValidationException::withMessages([
                'username' => 'Invalid response type',
            ]);
        }

        $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check(
            $authenticatorAttestationResponse,
            PublicKeyCredentialCreationOptions::createFromArray(
                session(self::CREDENTIAL_CREATION_OPTIONS_SESSION_KEY)
            ),
            $serverRequest
        );

        $request->session()->forget(self::CREDENTIAL_CREATION_OPTIONS_SESSION_KEY);

        $user = User::create([
            'username' => $publicKeyCredentialSource->getUserHandle(),
        ]);

        $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);

        Auth::login($user);

        return [
            'verified' => true,
        ];
    }
}
