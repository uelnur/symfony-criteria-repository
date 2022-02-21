<?php

namespace Uelnur\SymfonyCriteriaRepository;

interface CriteriaInterface {
    public function withID(mixed $id): self;

    public function alsoWithID(mixed $id): self;

    public function withoutID(mixed $id): self;

    public function alsoWithoutID(mixed $id): self;

    public function withSearchText(string $search): self;

    public function withMaxResult(int $limit): self;

    public function withResultOffset(int $offset = 0): self;

    public function withPagination(int $page = 1, ?int $perPage = null): self;

    public function orderedBy(string $field, bool $asc): self;
}
