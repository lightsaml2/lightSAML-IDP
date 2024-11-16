<?php

/*
 * This file is part of the LightSAML-IDP package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the GPL-3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LightSaml\Idp\Action\Profile\Outbound\StatusResponse;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\SamlConstants;
use Psr\Log\LoggerInterface;

class SetStatusAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected string $statusCode = SamlConstants::STATUS_SUCCESS, protected ?string $statusMessage = null)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context): void {
        $statusResponse = MessageContextHelper::asStatusResponse($context->getOutboundContext());

        $statusResponse->setStatus(new Status(new StatusCode($this->statusCode), $this->statusCode));
    }
}
