<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Core\Authorization\Voter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterManager;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterSubject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Throwable;

final class FilterVoter implements VoterInterface
{
    protected EntityManagerInterface $em;

    protected FilterManager $filterManager;

    protected bool $throws;

    public function __construct(EntityManagerInterface $em, FilterManager $filterManager, bool $throws)
    {
        $this->em = $em;
        $this->filterManager = $filterManager;
        $this->throws = $throws;
    }

    /**
     * @throws Throwable
     */
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            try {
                $voteOnAttribute = $this->voteOnAttribute((string) $attribute, $subject, $token);

                if (self::ACCESS_GRANTED === $voteOnAttribute) {
                    return self::ACCESS_GRANTED;
                } elseif (self::ACCESS_DENIED === $voteOnAttribute) {
                    $vote = self::ACCESS_DENIED;
                }
            } catch (Throwable $e) {
                if ($this->throws) {
                    throw $e;
                }
            }
        }

        return $vote;
    }

    /**
     * @psalm-return self::ACCESS_*
     */
    private function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): int
    {
        // Check, that subject if Doctrine entity
        if (!is_object($subject)) {
            return self::ACCESS_ABSTAIN;
        }

        if (!$this->em->getMetadataFactory()->hasMetadataFor($subject::class)) {
            return self::ACCESS_ABSTAIN;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $metadata = $this->em->getMetadataFactory()->getMetadataFor($subject::class);

        $filterSubject = new FilterSubject($metadata->getName(), 'entity');
        $qb = $this->createQueryBuilder($filterSubject, $this->getIdentifierValue($subject));

        $prevQuery = $qb->getQuery()->getDQL();

        $this->filterManager->filter($attribute, $qb, $filterSubject, $token->getUser());

        // If query was not modified, then abstain vote
        if ($qb->getQuery()->getDQL() === $prevQuery) {
            return self::ACCESS_ABSTAIN;
        }

        // Grant access, if it has filtered records
        return ((int) $qb->getQuery()->getSingleScalarResult()) > 0 ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }

    /**
     * @return array<string, mixed>
     */
    private function getIdentifierValue(object $subject): array
    {
        return $this->em->getClassMetadata($subject::class)->getIdentifierValues($subject);
    }

    /**
     * @param array<string, mixed> $id
     */
    private function createQueryBuilder(FilterSubject $filterSubject, array $id): QueryBuilder
    {
        $class = $filterSubject->getClassName();
        $alias = $filterSubject->getAlias();

        $qb = $this->em->createQueryBuilder();
        $qb->from($class, $alias)
            ->select(
                $qb->expr()->countDistinct(
                    ...array_map(
                        fn (string $f) => "{$alias}.{$f}",
                        array_keys($id)
                    )
                )
            )
        ;

        foreach ($id as $idField => $idValue) {
            $qb->andWhere("{$alias}.{$idField} = :{$alias}Identifier{$idField}")
                ->setParameter("{$alias}Identifier{$idField}", $idValue)
            ;
        }

        return $qb;
    }
}
