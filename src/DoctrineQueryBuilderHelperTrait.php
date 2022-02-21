<?php

namespace Uelnur\SymfonyCriteriaRepository;

use Doctrine\ORM\QueryBuilder;

trait DoctrineQueryBuilderHelperTrait {
    private static array $fieldNumber = [];

    public function whereFieldEqual(
        string $field,
        string|bool|int|null|array $value,
        QueryBuilder $qb,
        string|int|null $valueType = null,
    ): void {
        $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($field);

        if ( is_array($value) && count($value) > 1 ) {
            $qb
                ->andWhere($qb->expr()->in($field, ':'.$fieldName))
                ->setParameter($fieldName, $value, $valueType )
            ;
        }

        if ( is_array($value) && count($value) === 1 ) {
            $value = current($value);
        }

        if ( $value !== null ) {
            $qb
                ->andWhere($qb->expr()->eq($field, ':'.$fieldName))
                ->setParameter($fieldName, $value, $valueType )
            ;
        }
    }

    public function whereFieldNotEqual(
        string $field,
        string|bool|int|null|array $value,
        QueryBuilder $qb,
        string|int|null $valueType = null,
    ): void {
        $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($field);

        if ( is_array($value) && count($value) > 1 ) {
            $qb
                ->andWhere($qb->expr()->notIn($field, ':'.$fieldName))
                ->setParameter($fieldName, $value, $valueType )
            ;
        }

        if ( is_array($value) && count($value) === 1 ) {
            $value = current($value);
        }

        if ( $value !== null ) {
            $qb
                ->andWhere($qb->expr()->neq($field, ':'.$fieldName))
                ->setParameter($fieldName, $value, $valueType )
            ;
        }
    }

    public function whereFieldIsNull(
        string $field,
        bool|null $value,
        QueryBuilder $qb,
        string|int|null $valueType = null,
    ): void {
        if ( $value === null ) {
            return;
        }

        if ( $value ) {
            $qb->andWhere($qb->expr()->isNull($field));
        } else {
            $qb->andWhere($qb->expr()->isNotNull($field));
        }
    }

    public function whereFieldLike(
        string|array $field,
        string|null $value,
        QueryBuilder $qb,
        string|int|null $valueType = null,
    ): void {
        if ( $value === null ) {
            return;
        }

        $value = '%'.$value.'%';

        if ( is_array( $field ) ) {
            $likes = [];

            foreach ($field as $f) {
                $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($f);

                $likes[] = $qb->expr()->like(
                    $qb->expr()->lower($f),
                    $qb->expr()->lower(':'.$fieldName)
                );

                $qb->setParameter($fieldName, $value, $valueType );
            }

            $qb->andWhere(
                $qb->expr()->orX(...$likes)
            );
        } else {
            $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($field);

            $qb
                ->andWhere(
                    $qb->expr()->like(
                        $qb->expr()->lower($field),
                        $qb->expr()->lower(':'.$fieldName)
                    )
                )
                ->setParameter($fieldName, $value, $valueType )
            ;
        }
    }

    public function whereFieldBetween(
        string          $field,
        string|int|null $gte,
        string|int|null $lte,
        QueryBuilder    $qb,
        string|int|null $valueType = null,
    ): void {
        if ( $gte !== null && $lte !== null ) {
            $fieldNameGte = DoctrineQueryBuilderHelperTrait::getFieldName($field.'_gte');
            $fieldNameLte = DoctrineQueryBuilderHelperTrait::getFieldName($field.'_lte');

            $qb
                ->andWhere(
                    $qb->expr()->between(
                        $field,
                        ':'.$fieldNameGte,
                        ':'.$fieldNameLte,
                    )
                )
                ->setParameter($fieldNameGte, $gte, $valueType )
                ->setParameter($fieldNameLte, $lte, $valueType )
            ;

            return;
        }

        if ( $gte !== null ) {
            $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($field);

            $qb
                ->andWhere(
                    $qb->expr()->gte(
                        $field,
                        ':'.$fieldName,
                    )
                )
                ->setParameter($fieldName, $gte, $valueType )
            ;
        }

        if ( $lte !== null ) {
            $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($field);

            $qb
                ->andWhere(
                    $qb->expr()->lte(
                        $field,
                        ':'.$field,
                    )
                )
                ->setParameter($field, $lte, $valueType )
            ;
        }
    }

    public function whereFieldIsNullOrBetween(
        string          $field,
        string|int|null $gte,
        string|int|null $lte,
        QueryBuilder    $qb,
        string|int|null $valueType = null,
    ): void {
        $fieldName = DoctrineQueryBuilderHelperTrait::getFieldName($field);

        if ( $gte !== null ) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull($field),
                        $qb->expr()->gte(
                            $field,
                            ':'.$fieldName,
                        )
                    ),
                )
                ->setParameter($fieldName, $gte, $valueType )
            ;
        }

        if ( $lte !== null ) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull($field),
                        $qb->expr()->lte(
                            $field,
                            ':'.$fieldName,
                        )
                    ),
                )
                ->setParameter($fieldName, $lte, $valueType )
            ;
        }
    }

    private static function getFieldName(string $field): string {
        $field = str_replace('.', '_', $field);
        $number = (DoctrineQueryBuilderHelperTrait::$fieldNumber[$field] ?? 0) + 1;

        return $field . $number;
    }
}
