<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\SkeletonMapper\Repository;

use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface;
use Doctrine\SkeletonMapper\ObjectIdentityMap;

/**
 * Base class for object repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class ObjectRepository implements ObjectRepositoryInterface
{
    /**
     * @var \Doctrine\SkeletonMapper\Repository\ObjectDataRepositoryInterface
     */
    protected $objectDataRepository;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface
     */
    protected $objectHydrator;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectIdentityMap
     */
    protected $objectIdentityMap;

    /**
     * @param \Doctrine\SkeletonMapper\Repository\ObjectDataRepositoryInterface $objectDataRepository
     * @param \Doctrine\SkeletonMapper\ObjectFactory                            $objectFactory
     * @param \Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface         $objectHydrator
     * @param \Doctrine\SkeletonMapper\ObjectIdentityMap                        $objectIdentityMap
     */
    public function __construct(
        ObjectDataRepositoryInterface $objectDataRepository,
        ObjectFactory $objectFactory,
        ObjectHydratorInterface $objectHydrator,
        ObjectIdentityMap $objectIdentityMap)
    {
        $this->objectDataRepository = $objectDataRepository;
        $this->objectFactory = $objectFactory;
        $this->objectHydrator = $objectHydrator;
        $this->objectIdentityMap = $objectIdentityMap;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        $data = $this->objectDataRepository->find($id);

        if ($data === null) {
            return;
        }

        return $this->getOrCreateObject($data);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        $objectsData = $this->objectDataRepository->findAll();

        $objects = array();
        foreach ($objectsData as $objectData) {
            $objects[] = $this->getOrCreateObject($objectData);
        }

        return $objects;
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $objectsData = $this->objectDataRepository->findBy(
            $criteria, $orderBy, $limit, $offset
        );

        $objects = array();
        foreach ($objectsData as $objectData) {
            $objects[] = $this->getOrCreateObject($objectData);
        }

        return $objects;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $data = $this->objectDataRepository->findOneBy($criteria);

        if ($data === null) {
            return;
        }

        return $this->getOrCreateObject($data);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        $data = $this->objectDataRepository->findByObject($object);

        $this->objectHydrator->hydrate($object, $data);
    }

    /**
     * @param mixed $id
     * @param array $data
     *
     * @return object
     */
    private function getOrCreateObject(array $data)
    {
        $className = $this->getClassName();
        $object = $this->objectIdentityMap->tryGetById($className, $data);

        if (!$object) {
            $object = $this->objectFactory->create($className);
            $this->objectHydrator->hydrate($object, $data);

            $this->objectIdentityMap->addToIdentityMap($object, $data);
        }

        return $object;
    }
}