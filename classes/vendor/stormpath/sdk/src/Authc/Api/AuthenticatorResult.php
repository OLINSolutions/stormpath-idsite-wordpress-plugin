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

use Stormpath\Resource\ApiKey;
use Stormpath\Resource\Application;

class AuthenticatorResult
{

    protected $application;

    protected $apiKey;

    protected $accessToken;

    public function __construct(Application $application, ApiKey $apiKey, $accessToken = null)
    {
        $this->application = $application;

        $this->apiKey = $apiKey;

        if($accessToken) {
            $this->accessToken = $accessToken;
        }
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
