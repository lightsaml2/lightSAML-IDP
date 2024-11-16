<?php

/*
 * This file is part of the LightSAML-IDP package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the GPL-3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LightSaml\Idp\Action\Profile\Outbound\Response;

use LightSaml\Action\ActionInterface;
use LightSaml\Action\CompositeActionInterface;
use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use Psr\Log\LoggerInterface;

/**
 * Foreach of the given assertion actions, creates an assertion context, executes it, and if it has an assertion,
 * adds it to the Response in outbound context.
 */
class HandleAssertionsAction extends AbstractProfileAction implements CompositeActionInterface
{
    /**
     * @param ActionInterface[] $assertionActions
     */
    public function __construct(LoggerInterface $logger, protected array $assertionActions = [])
    {
        parent::__construct($logger);

        foreach ($assertionActions as $action) {
            $this->add($action);
        }
    }

    public function add(ActionInterface $assertionAction): static {
        $this->assertionActions[] = $assertionAction;

        return $this;
    }

    /**
     * @param callable $callable
     */
    public function map($callable): ?ActionInterface {
        foreach ($this->assertionActions as $k => $action) {
            $newAction = call_user_func($callable, $action);
            if ($newAction) {
                $this->assertionActions[$k] = $newAction;
            }
        }
    }

    protected function doExecute(ProfileContext $context): void {
        $response = MessageContextHelper::asResponse($context->getOutboundContext());

        foreach ($this->assertionActions as $index => $action) {
            $name = sprintf('assertion_%s', $index);
            /** @var AssertionContext $assertionContext */
            $assertionContext = $context->getSubContext($name, AssertionContext::class);
            $assertionContext->setId($index);

            $action->execute($assertionContext);

            if ($assertionContext->getEncryptedAssertion()) {
                $response->addEncryptedAssertion($assertionContext->getEncryptedAssertion());
            } elseif ($assertionContext->getAssertion()) {
                $response->addAssertion($assertionContext->getAssertion());
            } else {
                $this->logger->warning('No assertion was built', LogHelper::getActionContext($context, $this));
            }
        }
    }
}
