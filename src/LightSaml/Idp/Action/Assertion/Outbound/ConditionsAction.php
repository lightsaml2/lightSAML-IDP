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
use LightSaml\Model\Assertion\AudienceRestriction;
use LightSaml\Model\Assertion\Conditions;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates Conditions and AudienceRestriction.
 */
class ConditionsAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, protected TimeProviderInterface $timeProvider, protected int $expirationSeconds)
    {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context): void {
        $partyEntityDescriptor = $context->getProfileContext()->getPartyEntityDescriptor();

        $conditions = new Conditions();
        $conditions->setNotBefore($this->timeProvider->getTimestamp());
        $conditions->setNotOnOrAfter($conditions->getNotBeforeTimestamp() + $this->expirationSeconds);

        $audienceRestriction = new AudienceRestriction([
            $partyEntityDescriptor->getEntityID(),
        ]);
        $conditions->addItem($audienceRestriction);

        $context->getAssertion()->setConditions($conditions);
    }
}
