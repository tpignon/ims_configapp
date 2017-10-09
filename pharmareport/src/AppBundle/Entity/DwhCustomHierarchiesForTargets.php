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
     * @ORM\Column(name="product_market_hierarchy", type="string", length=255)
     */
    private $productMarketHierarchy;

    /**
     * @var string
     *
     * @ORM\Column(name="product_market_value", type="string", length=500)
     */
    private $productMarketValue;

    /**
     * @var string
     *
     * @ORM\Column(name="region_hierarchy", type="string", length=255)
     */
    private $regionHierarchy;

    /**
     * @var string
     *
     * @ORM\Column(name="region_value", type="string", length=500)
     */
    private $regionValue;


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
     * Set productMarketValue
     *
     * @param string $productMarketValue
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setProductMarketValue($productMarketValue)
    {
        $this->productMarketValue = $productMarketValue;

        return $this;
    }

    /**
     * Get productMarketValue
     *
     * @return string
     */
    public function getProductMarketValue()
    {
        return $this->productMarketValue;
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

    /**
     * Set regionValue
     *
     * @param string $regionValue
     *
     * @return DwhCustomHierarchiesForTargets
     */
    public function setRegionValue($regionValue)
    {
        $this->regionValue = $regionValue;

        return $this;
    }

    /**
     * Get regionValue
     *
     * @return string
     */
    public function getRegionValue()
    {
        return $this->regionValue;
    }
}
