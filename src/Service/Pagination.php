<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Pagination
 * @package App\Service
 */
class Pagination {
    private $entityClass;
    private $limit = 15;
    private $currentPage = 1;
    private $manager;


    /**
     * __construct
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function __construct(EntityManagerInterface $manager) {
        $this->manager = $manager;
    }

    /**
     * getData
     *
     * @return void
     */
    public function getData() {

        $offset = $this->currentPage * $this->limit - $this->limit;

        $repo = $this->manager->getRepository($this->entityClass);
        $data = $repo->findBy([], [], $this->limit, $offset);

        return $data;
    }

    /**
     * getPages
     *
     * @return void
     */
    public function getPages() {

        $repo = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findAll());

        $pages = ceil($total / $this->getLimit());

        return $pages;
    }

    /**
     * setPage
     *
     * @param mixed $page
     * @return void
     */
    public function setPage($page) {
        $this->currentPage = $page;

        return $this;
    }

    /**
     * getPage
     *
     * @return void
     */
    public function getPage() {
        return $this->currentPage;
    }

    /**
     * setLimit
     *
     * @param mixed $limit
     * @return void
     */
    public function setLimit($limit) {
        $this->limit = $limit;

        return $this;
    }

    /**
     * getLimit
     *
     * @return void
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * setEntityClass
     *
     * @param mixed $entityClass
     * @return void
     */
    public function setEntityClass($entityClass) {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * getEntityClass
     *
     * @return void
     */
    public function getEntityClass() {
        return $this->entityClass;
    }

}