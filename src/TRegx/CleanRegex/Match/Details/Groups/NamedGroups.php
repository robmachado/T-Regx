<?php
namespace TRegx\CleanRegex\Match\Details\Groups;

use TRegx\CleanRegex\Internal\Model\Match\IRawMatchOffset;

class NamedGroups extends AbstractMatchGroups
{
    public function __construct(IRawMatchOffset $match)
    {
        parent::__construct($match);
    }

    protected function filterGroupKey($nameOrIndex): bool
    {
        return is_string($nameOrIndex);
    }
}