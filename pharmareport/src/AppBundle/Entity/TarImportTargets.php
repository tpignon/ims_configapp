<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* TarImportTargets
*
* @ORM\Table(name="tar_import_targets")
* @ORM\Entity(repositoryClass="AppBundle\Repository\TarImportTargetsRepository")
*/
class TarImportTargets
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ptk;

    /**
     * @ORM\Column(name="client_output_id", type="integer")
     * @Assert\NotBlank(message="Column ClientoutputId should not be blank.")
     * @Assert\Choice(choices = {
     *    "91",
     *    "92",
     *    "93",
     *    "94",
     *    "97",
     *    "98",
     *    "1111",
     *    "2597",
     *    "3391",
     *    "3392",
     *    "3436",
     *    "3438",
     *    "3541",
     *    "3712",
     *    "3852",
     *    "3853",
     *    "3890",
     *    "3970",
     *    "3980",
     *    "4049",
     *    "4249",
     *    ""},
     *    message = "The ClientOutputID {{ value }} is not valid.")
     */
    private $clientOutputId;

    /**
     * @ORM\Column(name="product_market_level", type="string", length=500)
     * @Assert\NotBlank(message="Product Market Level should not be blank.")
     * @Assert\Type("string", message="The Product Market Level {{ value }} is not a valid {{ type }}.")
     */
    private $productMarketLevel;

    /**
     * @ORM\Column(name="region_level", type="string", length=500)
     * @Assert\NotBlank(message="The Region Level should not be blank.")
     * @Assert\Type("string", message="The Region Level {{ value }} is not a valid {{ type }}.")
     */
    private $regionLevel;

    /**
     * @ORM\Column(name="period", type="integer")
     * @Assert\NotBlank(message="Period should not be blank.")
     */
    private $period;

    /**
     * @ORM\Column(name="target_units", type="decimal", precision=25, scale=15, nullable=true)
     */
    private $targetUnits;

    /**
     * @ORM\Column(name="ms_units_target", type="decimal", precision=25, scale=15, nullable=true)
     */
    private $msUnitsTarget;

    /**
     * @ORM\Column(name="ms_value_target", type="decimal", precision=25, scale=15, nullable=true)
     */
    private $msValueTarget;

    /**
     * @ORM\Column(name="target_value", type="decimal", precision=25, scale=15, nullable=true)
     */
    private $targetValue;

    /**
     * @ORM\Column(name="target_status", type="string", length=255, nullable=true)
     */
    private $targetStatus;


    /**
     * Get ptk
     *
     * @return integer
     */
    public function getPtk()
    {
        return $this->ptk;
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
     * Get productMarketLevel
     *
     * @return string
     */
    public function getProductMarketLevel()
    {
        return $this->productMarketLevel;
    }

    /**
     * Get regionLevel
     *
     * @return string
     */
    public function getRegionLevel()
    {
        return $this->regionLevel;
    }

    /**
     * Get periodType
     *
     * @return string
     */
    public function getPeriodType()
    {
        return $this->periodType;
    }

    /**
     * Get period
     *
     * @return integer
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Get targetUnits
     *
     * @return decimal
     */
    public function getTargetUnits()
    {
        return $this->targetUnits;
    }

    /**
     * Get msUnitsTarget
     *
     * @return decimal
     */
    public function getMsUnitsTarget()
    {
        return $this->msUnitsTarget;
    }

    /**
     * Get msValueTarget
     *
     * @return decimal
     */
    public function getMsValueTarget()
    {
        return $this->msValueTarget;
    }

    /**
     * Get targetValue
     *
     * @return decimal
     */
    public function getTargetValue()
    {
        return $this->targetValue;
    }

    /**
     * Get targetStatus
     *
     * @return string
     */
    public function getTargetStatus()
    {
        return $this->targetStatus;
    }


    /**
     * Set clientOutputId
     *
     * @param integer $clientOutputId
     */
    public function setClientOutputId($clientOutputId)
    {
        $this->clientOutputId = $clientOutputId;
    }

    /**
     * Set productMarketLevel
     *
     * @param string $productMarketLevel
     */
    public function setProductMarketLevel($productMarketLevel)
    {
        $this->productMarketLevel = $productMarketLevel;
    }

    /**
     * Set regionLevel
     *
     * @param string $regionLevel
     */
    public function setRegionLevel($regionLevel)
    {
        $this->regionLevel = $regionLevel;
    }

    /**
     * Set periodType
     *
     * @param string $periodType
     */
    public function setPeriodType($periodType)
    {
        $this->periodType = $periodType;
    }

    /**
     * Set period
     *
     * @param integer $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * Set targetUnits
     *
     * @param decimal $targetUnits
     */
    public function setTargetUnits($targetUnits)
    {
        $this->targetUnits = $targetUnits;
    }

    /**
     * Set msUnitsTarget
     *
     * @param decimal $msUnitsTarget
     */
    public function setMsUnitsTarget($msUnitsTarget)
    {
        $this->msUnitsTarget = $msUnitsTarget;
    }

    /**
     * Set msValueTarget
     *
     * @param decimal $msValueTarget
     */
    public function setMsValueTarget($msValueTarget)
    {
        $this->msValueTarget = $msValueTarget;
    }

    /**
     * Set targetValue
     *
     * @param decimal $targetValue
     */
    public function setTargetValue($targetValue)
    {
        $this->targetValue = $targetValue;
    }

    /**
     * Set targetStatus
     *
     * @param string $targetStatus
     */
    public function setTargetStatus($targetStatus)
    {
        $this->targetStatus = $targetStatus;
    }


    public function toArray()
    {
        return array(
            $this->clientOutputId,
            $this->productMarketLevel,
            $this->regionLevel,
            $this->period,
            $this->targetUnits,
            $this->msUnitsTarget,
            $this->msValueTarget,
            $this->targetValue
        );
    }
}
