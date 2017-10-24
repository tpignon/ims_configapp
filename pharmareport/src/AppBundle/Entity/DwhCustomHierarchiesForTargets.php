<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DwhCustomHierarchiesForTargets
 *
 * @ORM\Table(name="dwh_custom_hierarchies_for_targets")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DwhCustomHierarchiesForTargetsRepository")
 */
class DwhCustomHierarchiesForTargets
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="client_output_id", type="integer")
     */
    private $clientOutputId;

    /**
     * @var string
     *
     * @ORM\Column(name="product_market_level", type="string", length=500)
     */
    private $productMarketLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="product_market_hierarchy", type="string", length=255)
     */
    private $productMarketHierarchy;

    /**
     * @var string
     *
     * @ORM\Column(name="region_level", type="string", length=500)
     */
    private $regionLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="region_hierarchy", type="string", length=255)
     */
    private $regionHierarchy;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set clientOutputId
     *
     * @param integer $clientOutputId
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setClientOutputId($clientOutputId)
    {
        $this->clientOutputId = $clientOutputId;

        return $this;
    }

    /**
     * Get clientOutputId
     *
     * @return integer
     */
    public function getClientOutputId()
    {
        return $this->clientOutputId;
    }

    /**
     * Set productMarketValue
     *
     * @param string $productMarketValue
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setProductMarketLevel($productMarketLevel)
    {
        $this->productMarketLevel = $productMarketLevel;

        return $this;
    }

    /**
     * Get productMarketValue
     *
     * @return string
     */
    public function getProductMarketLevel()
    {
        return $this->productMarketLevel;
    }

    /**
     * Set productMarketHierarchy
     *
     * @param string $productMarketHierarchy
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setProductMarketHierarchy($productMarketHierarchy)
    {
        $this->productMarketHierarchy = $productMarketHierarchy;

        return $this;
    }

    /**
     * Get productMarketHierarchy
     *
     * @return string
     */
    public function getProductMarketHierarchy()
    {
        return $this->productMarketHierarchy;
    }

    /**
     * Set regionValue
     *
     * @param string $regionValue
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setRegionLevel($regionLevel)
    {
        $this->regionLevel = $regionLevel;

        return $this;
    }

    /**
     * Get regionValue
     *
     * @return string
     */
    public function getRegionLevel()
    {
        return $this->regionLevel;
    }

    /**
     * Set regionHierarchy
     *
     * @param string $regionHierarchy
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setRegionHierarchy($regionHierarchy)
    {
        $this->regionHierarchy = $regionHierarchy;

        return $this;
    }

    /**
     * Get regionHierarchy
     *
     * @return string
     */
    public function getRegionHierarchy()
    {
        return $this->regionHierarchy;
    }
}
