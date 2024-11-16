<?php

/*
 * This file is part of the LightSAML-IDP package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the GPL-3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LightSaml\Idp\Action\Assertion\Outbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Resolver\Session\SessionProcessorInterface;
use Psr\Log\LoggerInterface;

class IdpSsoStateAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, private SessionProcessorInterface $sessionProcessor)
    {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context): void {
        if ($context->getAssertion()) {
            $this->sessionProcessor->processAssertions(
                [$context->getAssertion()],
                $context->getProfileContext()->getOwnEntityDescriptor()->getEntityID(),
                $context->getProfileContext()->getPartyEntityDescriptor()->getEntityID()
            );
        }
    }
}
