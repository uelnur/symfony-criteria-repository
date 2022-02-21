<?php

namespace Uelnur\SymfonyCriteriaRepository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria;

abstract class AbstractDoctrineRepository implements RepositoryInterface {
    use DoctrineQueryBuilderHelperTrait;

    public function __construct(protected EntityManagerInterface $em) {}

    abstract public function getEntityClass(): string;

    abstract public function getCriteriaClass(): string;

    final protected function standardQueryBuilderModifier(AbstractCriteria $criteria, QueryBuilder $qb): void {
        $idField = $this->getFieldName($this->getIdField());

        $this->whereFieldEqual($idField, $criteria->id, $qb);
        $this->whereFieldEqual($idField, $criteria->andId, $qb);

        $this->whereFieldNotEqual($idField, $criteria->notId, $qb);
        $this->whereFieldNotEqual($idField, $criteria->andNotId, $qb);
    }

    protected function getFieldName($field, ?string $alias = null): string {
        if ( !$alias ) {
            $alias = $this->getEntityAlias();
        }

        return $field . '.' . $alias;
    }

    protected function modifyQueryBuilder(AbstractCriteria $criteria, QueryBuilder $qb): void {
        $this->standardQueryBuilderModifier($criteria, $qb);
    }

    protected function getDefaultOrder(): array {
        return [];
    }

    protected function getOrderOptions(): array {
        return ['id'];
    }

    protected function getOrderAliases(): array {
        return [];
    }

    protected function getIdField(): string {
        return 'id';
    }

    protected function getEntityAlias(): string {
        return 'entity';
    }

    public function createCriteria(): AbstractCriteria {
        $className = $this->getCriteriaClass();

        return new $className;
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getResult(?AbstractCriteria $criteria = null): array {
        $qb = $this->createQueryBuilder($criteria);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getArrayResult(?AbstractCriteria $criteria = null): array {
        $qb = $this->createQueryBuilder($criteria);

        return $qb->getQuery()->getArrayResult();
    }

    public function getResultByIDs(array $ids): array {
        $criteria = $this->createCriteria()->withID($ids);

        return $this->getResult($criteria);
    }

    public function getArrayResultByIDs(array $ids): array {
        $criteria = $this->createCriteria()->withID($ids);

        return $this->getArrayResult($criteria);
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getDQL(?AbstractCriteria $criteria = null): ?string {
        $qb = $this->createQueryBuilder($criteria);

        return $qb->getQuery()->getDQL();
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getSQL(?AbstractCriteria $criteria = null): ?string {
        $qb = $this->createQueryBuilder($criteria);

        return $qb->getQuery()->getSQL();
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getQuery(?AbstractCriteria $criteria = null): ?Query {
        $qb = $this->createQueryBuilder($criteria);

        return $qb->getQuery();
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getQueryBuilder(?AbstractCriteria $criteria = null): ?QueryBuilder {
        return $this->createQueryBuilder($criteria);
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getSingleResult(?AbstractCriteria $criteria = null): ?object {
        $qb = $this->createQueryBuilder($criteria);

        try
        {
            $result = $qb->getQuery()->getSingleResult();
        }
        catch (NoResultException|NonUniqueResultException)
        {
            $result = null;
        }

        return $result;
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getSingleArrayResult(?AbstractCriteria $criteria = null): array {
        $qb = $this->createQueryBuilder($criteria);

        try
        {
            $result = $qb->getQuery()->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
        }
        catch (NoResultException|NonUniqueResultException)
        {
            $result = null;
        }

        return $result;
    }

    public function getSingleResultByID(mixed $id): ?object {
        $criteria = $this->createCriteria()->withID($id);

        return $this->getSingleResult($criteria);
    }

    public function getSingleArrayResultByID(mixed $id): ?array {
        $criteria = $this->createCriteria()->withID($id);

        return $this->getSingleArrayResult($criteria);
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getCount(?AbstractCriteria $criteria = null, bool $distinct = false): int {
        $qb = $this
            ->createQueryBuilder($criteria)
            ->select(sprintf('COUNT(%s) cn', $this->getEntityAlias()))
            ->distinct($distinct)
            ->setMaxResults(1);

        $criteria = clone $criteria;
        $criteria->clearOrderBy([]);

        try
        {
            $result = $qb->getQuery()->getSingleScalarResult();
        }
        catch (NoResultException|NonUniqueResultException)
        {
            $result = 0;
        }

        return $result;
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    public function getIds(?AbstractCriteria $criteria = null, bool $distinct = false): array {
        return $this->getField($this->getIdField(), $this->getEntityAlias(), $criteria, $distinct);
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    protected function getField(string $field, ?string $alias = null, ?AbstractCriteria $criteria = null, bool $distinct = false): array {
        if ( !$alias ) {
            $alias = $this->getEntityAlias();
        }

        $qb = $this
            ->createQueryBuilder($criteria)
            ->select(sprintf('%s.%s as %s', $alias, $field, $field ))
            ->distinct($distinct);

        $result = $qb->getQuery()->getArrayResult();

        return array_column($result, $this->getIdField());
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    protected function getDefaultCriteria(?AbstractCriteria $criteria = null): AbstractCriteria {
        $criteriaClass = $this->getCriteriaClass();

        if ( $criteria && !$criteria instanceof $criteriaClass ) {
            throw new InvalidCriteria($criteria, $criteriaClass);
        }

        if ( !$criteria ) {
            $criteria = new $criteriaClass;
        }

        if ( !$criteria instanceof AbstractCriteria ) {
            throw new InvalidCriteria($criteria, AbstractCriteria::class);
        }

        return $criteria;
    }

    /**
     * @throws \Uelnur\SymfonyCriteriaRepository\Exception\InvalidCriteria
     */
    protected function createQueryBuilder(?AbstractCriteria $criteria): QueryBuilder {
        $criteria = $this->getDefaultCriteria($criteria);

        $alias = $this->getEntityAlias();

        $qb = $this->em->createQueryBuilder()
           ->from($this->getEntityClass(), $alias)
           ->select($alias)
        ;

        $this->modifyQueryBuilder($criteria, $qb);
        $this->processOrderBy($criteria, $qb);
        $this->processPagination($criteria, $qb);

        return $qb;
    }

    protected function processPagination(AbstractCriteria $criteria, QueryBuilder $qb): void
    {
        if ( $criteria->limit )
        {
            $qb->setMaxResults($criteria->limit);
        }

        if ( $criteria->offset )
        {
            $qb->setFirstResult($criteria->offset);
        }
    }

    protected function processOrderBy(AbstractCriteria $criteria, QueryBuilder $qb): void
    {
        $orderBy = $criteria->getOrderBy();

        if ( empty($orderBy) ) {
            $orderBy = $this->getDefaultOrder();
        }

        foreach ($orderBy as $property => $asc)
        {
            $this->orderBy($property, $asc, $qb);
        }
    }

    protected function orderBy(string $property, bool $asc, QueryBuilder $qb): void
    {
        $fields = array_merge($this->getOrderOptions());

        if ( !in_array($property, $fields, false) ) {
            return;
        }

        $aliases = $this->getOrderAliases();
        $property = $aliases[$property] ?? $property;

        if (!str_contains($property, '.')) {
            $property = $this->getEntityAlias() . '.' . $property;
        }

        $qb->addOrderBy($property, $asc ? 'ASC' : 'DESC');
    }
}
