<?php

namespace App\Models\UploadTypes;

use Closure;

abstract class BaseUploadType
{
    protected string $baseFolder;
    protected string $table;
    protected string $column;
    protected string $identifierField;
    protected ?string $defaultValue;
    protected int $maxSizeMb;
    protected ?Closure $fileNameFormat;
    protected bool $appendToArray;
    protected ?int $maxArraySize;
    protected bool $allowUploadWithoutRecord;

    public function __construct(
        string $baseFolder,
        string $table,
        string $column,
        string $identifierField,
        ?string $defaultValue,
        int $maxSizeMb,
        ?Closure $fileNameFormat = null,
        bool $appendToArray = false,
        ?int $maxArraySize = null,
        bool $allowUploadWithoutRecord = false
    ) {
        $this->baseFolder = $baseFolder;
        $this->table = $table;
        $this->column = $column;
        $this->identifierField = $identifierField;
        $this->defaultValue = $defaultValue;
        $this->maxSizeMb = $maxSizeMb;
        $this->fileNameFormat = $fileNameFormat;
        $this->appendToArray = $appendToArray;
        $this->maxArraySize = $maxArraySize;
        $this->allowUploadWithoutRecord = $allowUploadWithoutRecord;
    }

    public function getFolder(string $brandSymbol): string
    {
        return "{$brandSymbol}/{$this->baseFolder}";
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getIdentifierField(): string
    {
        return $this->identifierField;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function getMaxSizeMb(): int
    {
        return $this->maxSizeMb;
    }

    public function getFileNameFormat(): ?Closure
    {
        return $this->fileNameFormat;
    }

    public function shouldAppendToArray(): bool
    {
        return $this->appendToArray;
    }

    public function getMaxArraySize(): ?int
    {
        return $this->maxArraySize;
    }

    public function allowUploadWithoutRecord(): bool
    {
        return $this->allowUploadWithoutRecord;
    }
}