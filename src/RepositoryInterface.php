<?php

namespace Uelnur\SymfonyCriteriaRepository;

interface RepositoryInterface {
    public function createCriteria(): AbstractCriteria;

    public function getResult(?AbstractCriteria $criteria = null): array;

    public function getArrayResult(?AbstractCriteria $criteria = null): array;


    public function getResultByIDs(array $ids): array;

    public function getArrayResultByIDs(array $ids): array;


    public function getSingleResult(?AbstractCriteria $criteria = null): ?object;

    public function getSingleArrayResult(?AbstractCriteria $criteria = null): array;


    public function getSingleResultByID(mixed $id): ?object;

    public function getSingleArrayResultByID(mixed $id): ?array;


    public function getCount(?AbstractCriteria $criteria = null, bool $distinct = false): ?int;

    public function getIds(?AbstractCriteria $criteria = null, bool $distinct = false): array;
}
