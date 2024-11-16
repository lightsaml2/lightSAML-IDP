<?php

/*
 * This file is part of the LightSAML-IDP package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the GPL-3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LightSaml\Idp\Builder\Profile\WebBrowserSso\Idp;

use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Profile\AbstractProfileBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Idp\Builder\Action\Profile\SingleSignOn\Idp\SsoIdpReceiveRequestActionBuilder;
use LightSaml\Profile\Profiles;

class SsoIdpReceiveAuthnRequestProfileBuilder extends AbstractProfileBuilder
{

    protected function getProfileId(): string {
        return Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST;
    }

    protected function getProfileRole(): string {
        return ProfileContext::ROLE_IDP;
    }

    protected function getActionBuilder(): ActionBuilderInterface|SsoIdpReceiveRequestActionBuilder {
        return new SsoIdpReceiveRequestActionBuilder($this->container);
    }
}
