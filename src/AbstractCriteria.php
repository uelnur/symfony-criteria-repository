<?php

namespace Uelnur\SymfonyCriteriaRepository;

abstract class AbstractCriteria implements CriteriaInterface {
    public mixed $id = null;

    public mixed $andId = null;

    public mixed $notId = null;

    public mixed $andNotId = null;

    public ?string $search = null;

    public ?int $limit = null;

    public ?int $offset = null;

    protected array $orderBy = [];

    public function withID(mixed $id): self {
        $this->id = $id;

        return $this;
    }

    public function alsoWithID(mixed $id): self {
        $this->andId = $id;

        return $this;
    }

    public function withoutID(mixed $id): self {
        $this->notId = $id;

        return $this;
    }

    public function alsoWithoutID(mixed $id): self {
        $this->andNotId = $id;

        return $this;
    }

    public function withSearchText(string $search): self {
        $this->search = $search;

        return $this;
    }

    public function withMaxResult(int $limit): self {
        $this->limit = $limit;

        return $this;
    }

    public function withResultOffset(int $offset = 0): self {
        $this->offset = $offset;

        return $this;
    }

    public function withPagination(int $page = 1, ?int $perPage = null): self {
        return $this;
    }

    public function orderedBy(string $field, bool $asc = true): self {
        $this->orderBy[$field] = $asc;

        return $this;
    }

    public function getOrderBy(): array {
        return $this->orderBy;
    }

    public function clearOrderBy(): void {
        $this->orderBy = [];
    }
}
