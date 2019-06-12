<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds\Contracts;

interface IdGenerator
{
    public function generateId();

    public function persistedId();

    public function humanReadableId();
}
