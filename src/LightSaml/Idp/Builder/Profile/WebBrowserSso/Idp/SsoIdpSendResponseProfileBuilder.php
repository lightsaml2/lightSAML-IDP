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

use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Profile\AbstractProfileBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Idp\Builder\Action\Profile\SingleSignOn\Idp\SsoIdpSendResponseActionBuilder;
use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\Endpoint;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Profile\Profiles;

class SsoIdpSendResponseProfileBuilder extends AbstractProfileBuilder
{
    private EntityDescriptor|null $partyEntityDescriptor;
    private TrustOptions|null $partyTrustOptions;
    private Endpoint|null $endpoint;
    private SamlMessage|null $message;
    private string|null $relayState;

    public function __construct(BuildContainerInterface $buildContainer, private array $assertionBuilders, private string $entityId)
    {
        parent::__construct($buildContainer);

        $this->entityId = $entityId;
        foreach ($assertionBuilders as $builder) {
            $this->addAssertionBuilder($builder);
        }
    }

    public function setPartyEntityDescriptor(EntityDescriptor $entityDescriptor): static {
        $this->partyEntityDescriptor = $entityDescriptor;

        return $this;
    }

    public function setPartyTrustOptions(TrustOptions $partyTrustOptions): static {
        $this->partyTrustOptions = $partyTrustOptions;

        return $this;
    }

    public function setEndpoint(Endpoint $endpoint): static {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function setMessage(SamlMessage $message): static {
        $this->message = $message;

        return $this;
    }

    public function setRelayState(string $relayState): static {
        $this->relayState = $relayState;

        return $this;
    }

    private function addAssertionBuilder(ActionBuilderInterface $assertionBuilder): void {
        $this->assertionBuilders[] = $assertionBuilder;
    }

    protected function getProfileId(): string {
        return Profiles::SSO_IDP_SEND_RESPONSE;
    }

    protected function getProfileRole(): string {
        return ProfileContext::ROLE_IDP;
    }

    protected function getActionBuilder(): SsoIdpSendResponseActionBuilder {
        $result = new SsoIdpSendResponseActionBuilder($this->container);

        foreach ($this->assertionBuilders as $assertionAction) {
            $result->addAssertionBuilder($assertionAction);
        }

        return $result;
    }


    public function buildContext(): ProfileContext {
        $result = parent::buildContext();

        $result->getPartyEntityContext()->setEntityId($this->entityId);

        if ($this->partyEntityDescriptor) {
            $result->getPartyEntityContext()->setEntityDescriptor($this->partyEntityDescriptor);
        }

        if ($this->partyTrustOptions) {
            $result->getPartyEntityContext()->setTrustOptions($this->partyTrustOptions);
        }

        if ($this->endpoint) {
            $result->getEndpointContext()->setEndpoint($this->endpoint);
        }

        if ($this->message) {
            $result->getInboundContext()->setMessage($this->message);
        }

        if ($this->relayState) {
            $result->setRelayState($this->relayState);
        }

        return $result;
    }
}
