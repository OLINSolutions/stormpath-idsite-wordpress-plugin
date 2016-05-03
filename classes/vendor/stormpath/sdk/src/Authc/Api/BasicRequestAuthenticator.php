<?php
/*
 * Copyright 2016 Stormpath, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Stormpath\Authc\Api;

use Stormpath\Exceptions\RequestAuthenticatorException;

class BasicRequestAuthenticator extends InternalRequestAuthenticator implements RequestAuthenticator
{

    public function authenticate(Request $request)
    {
        if (!$this->application)
            throw new \InvalidArgumentException('The application must be set.');

        $apiKey = $this->getApiKeyById($request);

        if($this->isValidApiKey($request, $apiKey))
        {
            $account = $apiKey->account;
        }

        if($this->isValidAccount($account))
        {
            return new BasicAuthenticationResult($this->application, $apiKey);
        }


    }


}