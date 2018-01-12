<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* TarCurrentTargets
*
* @ORM\Table(name="tar_current_targets")
* @ORM\Entity(repositoryClass="AppBundle\Repository\TarCurrentTargetsRepository")
*/
class TarCurrentTargets
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ptk;
    /**
     * @ORM\Column(name="client_output_id", type="integer")
     */
    private $clientOutputId;
    /**
     * @ORM\Column(name="product_market_level", type="string", length=500)
     */
    private $productMarketLevel;
    /**
     * @ORM\Column(name="region_level", type="string", length=500)
     */
    private $regionLevel;
    /**
     * @ORM\Column(name="period", type="integer")
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

}
