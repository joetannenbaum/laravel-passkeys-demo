<?php

namespace App\Auth;

use App\Models\Authenticator;
use App\Models\User;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class CredentialSourceRepository implements PublicKeyCredentialSourceRepository
{
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        ray('findOneByCredentialId');
        ray($publicKeyCredentialId);
        ray(base64_encode($publicKeyCredentialId));

        $authenticator = Authenticator::where('credential_id', base64_encode($publicKeyCredentialId))->first();

        if (!$authenticator) {
            return null;
        }

        ray($authenticator);

        return PublicKeyCredentialSource::createFromArray($authenticator->public_key);
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        ray('findAllForUserEntity');
        ray($publicKeyCredentialUserEntity);
        ray($publicKeyCredentialUserEntity->getName());
        ray($publicKeyCredentialUserEntity->getId());

        return User::with('authenticators')->where('id', $publicKeyCredentialUserEntity->getId())->first()->authenticators->toArray();
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        ray('saveCredentialSource');
        ray($publicKeyCredentialSource);

        $user = User::where('id', $publicKeyCredentialSource->getUserHandle())->firstOrFail();

        $user->authenticators()->save(new Authenticator([
            'credential_id' => $publicKeyCredentialSource->getPublicKeyCredentialId(),
            'public_key' => $publicKeyCredentialSource->jsonSerialize(),
        ]));
    }
}
