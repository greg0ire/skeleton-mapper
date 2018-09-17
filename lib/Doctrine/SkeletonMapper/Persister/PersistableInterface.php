<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

/**
 * Interface persistable objects must implement.
 */
interface PersistableInterface
{
    /**
     * @return array
     */
    public function preparePersistChangeSet();

    /**
     * @param \Doctrine\SkeletonMapper\UnitOfWork\ChangeSet $changeSet
     *
     * @return array
     */
    public function prepareUpdateChangeSet(ChangeSet $changeSet);
}
