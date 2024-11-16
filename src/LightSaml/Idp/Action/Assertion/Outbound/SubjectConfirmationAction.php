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
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Assertion\SubjectConfirmation;
use LightSaml\Model\Assertion\SubjectConfirmationData;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\SamlConstants;
use Psr\Log\LoggerInterface;

/**
 * Creates SubjectConfirmation and creates Subject if not already created.
 */
class SubjectConfirmationAction extends AbstractAssertionAction
{
    public function __construct(
        LoggerInterface $logger,
        protected TimeProviderInterface $timeProvider,
        protected int $expirationSeconds
    ) {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context): void {
        $profileContext = $context->getProfileContext();
        $inboundMessage = $profileContext->getInboundContext()->getMessage();
        $endpoint = $profileContext->getEndpoint();

        $data = new SubjectConfirmationData();
        if ($inboundMessage) {
            $data->setInResponseTo($inboundMessage->getID());
        }
        $data->setAddress($profileContext->getHttpRequest()->getClientIp());
        $data->setNotOnOrAfter($this->timeProvider->getTimestamp() + $this->expirationSeconds);
        $data->setRecipient($endpoint->getLocation());

        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod(SamlConstants::CONFIRMATION_METHOD_BEARER);
        $subjectConfirmation->setSubjectConfirmationData($data);

        if (null === $context->getAssertion()->getSubject()) {
            $context->getAssertion()->setSubject(new Subject());
        }

        $context->getAssertion()->getSubject()->addSubjectConfirmation($subjectConfirmation);
    }
}
