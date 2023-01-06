<?php

namespace App\Http\Controllers;

use App\Auth\CredentialSourceRepository;
use App\Models\User;
use Cose\Algorithms;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class RegistrationController extends Controller
{
    public function generateOptions(Request $request)
    {
        $rpEntity = PublicKeyCredentialRpEntity::create(
            config('app.name'),
            parse_url(config('app.url'), PHP_URL_HOST),
            null,
        );

        $user = User::firstOrNew([
            'email' => $request->input('username'),
        ]);

        if (!$user->exists) {
            $user->name = $request->input('username');
            $user->password = Str::random(32);
            $user->save();
        }

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->email,
            $user->id,
            $user->name,
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
            ->excludeCredentials(
                ...$user->authenticators->map(
                    fn ($authenticator) => PublicKeyCredentialDescriptor::create(
                        PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                        $authenticator->credential_id,
                    )
                )->toArray()
            );

        $serializedPublicKeyCredentialCreationOptions = $publicKeyCredentialCreationOptions->jsonSerialize();

        if (!isset($serializedPublicKeyCredentialCreationOptions['excludeCredentials'])) {
            // The JS side needs this, so let's set it up for success with an empty array
            $serializedPublicKeyCredentialCreationOptions['excludeCredentials'] = [];
        }

        $request->session()->put('publicKeyCredentialCreationOptions', $serializedPublicKeyCredentialCreationOptions);

        return $serializedPublicKeyCredentialCreationOptions;
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
            abort(403, 'Invalid response type');
        }

        $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check(
            $authenticatorAttestationResponse,
            PublicKeyCredentialCreationOptions::createFromArray(session('publicKeyCredentialCreationOptions')),
            $serverRequest
        );

        $request->session()->forget('publicKeyCredentialCreationOptions');

        $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);
    }
}
