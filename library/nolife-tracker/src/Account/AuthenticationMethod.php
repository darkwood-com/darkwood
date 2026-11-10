<?php

namespace App\Account;

enum AuthenticationMethod: string
{
    case None = 'none';
    case ExistingCli = 'existing_cli';
    case OfficialApi = 'official_api';
    case OsCredentialStore = 'os_credential_store';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::ExistingCli => 'Existing CLI',
            self::OfficialApi => 'Official API',
            self::OsCredentialStore => 'OS credential store',
        };
    }
}
