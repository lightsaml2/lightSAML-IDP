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
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\EntityIdCriteria;
use LightSaml\Credential\Criteria\MetadataCriteria;
use LightSaml\Credential\Criteria\UsageCriteria;
use LightSaml\Credential\UsageType;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Assertion\EncryptedAssertionWriter;
use LightSaml\Resolver\Credential\CredentialResolverInterface;
use LightSaml\SamlConstants;
use Psr\Log\LoggerInterface;

class EncryptAssertionAction extends AbstractAssertionAction
{
    /** @var CredentialResolverInterface */
    protected $credentialResolver;

    public function __construct(LoggerInterface $logger, CredentialResolverInterface $credentialResolver)
    {
        parent::__construct($logger);

        $this->credentialResolver = $credentialResolver;
    }

    /**
     * @return void
     */
    protected function doExecute(AssertionContext $context)
    {
        $profileContext = $context->getProfileContext();
        $trustOptions = $profileContext->getTrustOptions();
        if (false === $trustOptions->getEncryptAssertions()) {
            return;
        }

        if (null == $assertion = $context->getAssertion()) {
            throw new LightSamlContextException($context, 'Assertion for encryption is not set');
        }
        $context->setAssertion(null);

        $query = $this->credentialResolver->query();
        $query
            ->add(new EntityIdCriteria($profileContext->getPartyEntityDescriptor()->getEntityID()))
            ->add(new MetadataCriteria(
                ProfileContext::ROLE_IDP === $profileContext->getOwnRole()
                ? MetadataCriteria::TYPE_SP
                : MetadataCriteria::TYPE_IDP,
                SamlConstants::PROTOCOL_SAML2
            ))
            ->add(new UsageCriteria(UsageType::ENCRYPTION))
        ;
        $query->resolve();

        /** @var CredentialInterface $credential */
        $credential = $query->firstCredential();
        if (null == $credential) {
            throw new LightSamlContextException($context, 'Unable to resolve encrypting credential');
        }
        if (null == $credential->getPublicKey()) {
            throw new LightSamlContextException($context, 'Credential resolved for assertion encryption does not have a public key');
        }

        $encryptedAssertionWriter = new EncryptedAssertionWriter(
            $trustOptions->getBlockEncryptionAlgorithm(),
            $trustOptions->getKeyTransportEncryptionAlgorithm()
        );
        $encryptedAssertionWriter->encrypt($assertion, $credential->getPublicKey());

        $context->setEncryptedAssertion($encryptedAssertionWriter);
    }
}
