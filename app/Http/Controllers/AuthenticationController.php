<?php

namespace App\Http\Controllers;

use App\Auth\CredentialSourceRepository;
use App\Models\User;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES256K;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\EdDSA\Ed256;
use Cose\Algorithm\Signature\EdDSA\Ed512;
use Cose\Algorithm\Signature\RSA\PS256;
use Cose\Algorithm\Signature\RSA\PS384;
use Cose\Algorithm\Signature\RSA\PS512;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithm\Signature\RSA\RS384;
use Cose\Algorithm\Signature\RSA\RS512;
use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class AuthenticationController extends Controller
{
    public function generateOptions(Request $request)
    {
        $user = User::where('email', $request->input('username'))->firstOrFail();

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->email,
            (string) $user->id,
            $user->name,
            null,
        );

        $publicKeyCredentialSourceRepository = new CredentialSourceRepository();

        $registeredAuthenticators = $publicKeyCredentialSourceRepository->findAllForUserEntity($userEntity);

        // We don’t need the Credential Sources, just the associated Descriptors
        $allowedCredentials = collect($registeredAuthenticators)
            ->pluck('public_key')
            ->map(fn ($publicKey) => PublicKeyCredentialSource::createFromArray($publicKey))
            ->map(
                fn (PublicKeyCredentialSource $credential): PublicKeyCredentialDescriptor => $credential->getPublicKeyCredentialDescriptor()
            )
            ->toArray();

        $publicKeyCredentialRequestOptions =
            PublicKeyCredentialRequestOptions::create(
                random_bytes(32) // Challenge
            )
            ->allowCredentials(...$allowedCredentials);

        $serializedPublicKeyCredentialRequestOptions = $publicKeyCredentialRequestOptions->jsonSerialize();

        $request->session()->put('publicKeyCredentialRequestOptions', $serializedPublicKeyCredentialRequestOptions);

        return $serializedPublicKeyCredentialRequestOptions;
    }

    public function verify(Request $request, ServerRequestInterface $serverRequest)
    {
        $publicKeyCredentialSourceRepository = new CredentialSourceRepository();
        $tokenBindingHandler = IgnoreTokenBindingHandler::create();

        $attestationStatementSupportManager = AttestationStatementSupportManager::create();

        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        $extensionOutputCheckerHandler = ExtensionOutputCheckerHandler::create();

        $algorithmManager = Manager::create()->add(
            ES256::create(),
            ES256K::create(),
            ES384::create(),
            ES512::create(),
            RS256::create(),
            RS384::create(),
            RS512::create(),
            PS256::create(),
            PS384::create(),
            PS512::create(),
            Ed256::create(),
            Ed512::create(),
        );

        $authenticatorAttestationResponseValidator = AuthenticatorAssertionResponseValidator::create(
            $publicKeyCredentialSourceRepository,
            $tokenBindingHandler,
            $extensionOutputCheckerHandler,
            $algorithmManager,
        );

        $attestationObjectLoader = AttestationObjectLoader::create(
            $attestationStatementSupportManager
        );

        $publicKeyCredentialLoader = PublicKeyCredentialLoader::create(
            $attestationObjectLoader
        );

        $publicKeyCredential = $publicKeyCredentialLoader->load(json_encode($request->all()));

        $authenticatorAssertionResponse = $publicKeyCredential->getResponse();

        if (!$authenticatorAssertionResponse instanceof AuthenticatorAssertionResponse) {
            abort(403, 'Invalid response type');
        }

        $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check(
            $publicKeyCredential->getRawId(),
            $authenticatorAssertionResponse,
            PublicKeyCredentialRequestOptions::createFromArray(session('publicKeyCredentialRequestOptions')),
            $serverRequest,
            $authenticatorAssertionResponse->getUserHandle(),
        );

        $request->session()->forget('publicKeyCredentialRequestOptions');

        ray('log user in now!!!!');
    }
}
