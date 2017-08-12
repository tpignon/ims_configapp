<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DwhDimGeoSalesRep
 *
 * @ORM\Table(name="dwh_d_ph_geo_sales_rep")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DwhDimGeoSalesRepRepository")
 */
class DwhDimGeoSalesRep
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
     * @ORM\Column(name="ds_bk", type="integer")
     */
    private $clientOutputId;

    /**
     * @var string
     *
     * @ORM\Column(name="mkt_level1", type="string", length=150, nullable=true)
     */
    private $geoTeam;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level1", type="string", length=100, nullable=true)
     */
    private $geoLevel1;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level2", type="string", length=100, nullable=true)
     */
    private $geoLevel2;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level3", type="string", length=100, nullable=true)
     */
    private $geoLevel3;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level4", type="string", length=100, nullable=true)
     */
    private $geoLevel4;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level5", type="string", length=100, nullable=true)
     */
    private $geoLevel5;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level6", type="string", length=100, nullable=true)
     */
    private $geoLevel6;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_level7", type="string", length=100, nullable=true)
     */
    private $geoLevel7;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level1_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel1;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level2_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel2;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level3_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel3;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level4_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel4;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level5_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel5;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level6_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel6;

    /**
     * @var string
     *
     * @ORM\Column(name="slsrep_level7_bk", type="string", length=255, nullable=true)
     */
    private $salesRepLevel7;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getClientOutputId()
    {
        return $this->clientOutputId;
    }

    /**
     * @return string
     */
    public function getGeoTeam()
    {
        return $this->geoTeam;
    }

    /**
     * @return string
     */
    public function getGeoLevel1()
    {
        return $this->geoLevel1;
    }

    /**
     * @return string
     */
    public function getGeoLevel2()
    {
        return $this->geoLevel2;
    }

    /**
     * @return string
     */
    public function getGeoLevel3()
    {
        return $this->geoLevel3;
    }

    /**
     * @return string
     */
    public function getGeoLevel4()
    {
        return $this->geoLevel4;
    }

    /**
     * @return string
     */
    public function getGeoLevel5()
    {
        return $this->geoLevel5;
    }

    /**
     * @return string
     */
    public function getGeoLevel6()
    {
        return $this->geoLevel6;
    }

    /**
     * @return string
     */
    public function getGeoLevel7()
    {
        return $this->geoLevel7;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel1()
    {
        return $this->salesRepLevel1;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel2()
    {
        return $this->salesRepLevel2;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel3()
    {
        return $this->salesRepLevel3;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel4()
    {
        return $this->salesRepLevel4;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel5()
    {
        return $this->salesRepLevel5;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel6()
    {
        return $this->salesRepLevel6;
    }

    /**
     * @return string
     */
    public function getSalesRepLevel7()
    {
        return $this->salesRepLevel7;
    }

    /**
     * @param integer $clientOutputId
     * @return DwhDimGeoSalesRep
     */
    public function setClientOutputId($clientOutputId)
    {
        $this->clientOutputId = $clientOutputId;

        return $this;
    }

    /**
     * @param string $geoTeam
     * @return DwhDimGeoSalesRep
     */
    public function setGeoTeam($geoTeam)
    {
        $this->geoTeam = $geoTeam;

        return $this;
    }

    /**
     * @param string $geoLevel1
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel1($geoLevel1)
    {
        $this->geoLevel1 = $geoLevel1;

        return $this;
    }

    /**
     * @param string $geoLevel2
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel2($geoLevel2)
    {
        $this->geoLevel2 = $geoLevel2;

        return $this;
    }

    /**
     * @param string $geoLevel3
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel3($geoLevel3)
    {
        $this->geoLevel3 = $geoLevel3;

        return $this;
    }

    /**
     * @param string $geoLevel4
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel4($geoLevel4)
    {
        $this->geoLevel4 = $geoLevel4;

        return $this;
    }

    /**
     * @param string $geoLevel5
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel5($geoLevel5)
    {
        $this->geoLevel5 = $geoLevel5;

        return $this;
    }

    /**
     * @param string $geoLevel6
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel6($geoLevel6)
    {
        $this->geoLevel6 = $geoLevel6;

        return $this;
    }

    /**
     * @param string $geoLevel7
     * @return DwhDimGeoSalesRep
     */
    public function setGeoLevel7($geoLevel7)
    {
        $this->geoLevel7 = $geoLevel7;

        return $this;
    }

    /**
     * @param string $salesRepLevel1
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel1($salesRepLevel1)
    {
        $this->salesRepLevel1 = $salesRepLevel1;

        return $this;
    }

    /**
     * @param string $salesRepLevel2
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel2($salesRepLevel2)
    {
        $this->salesRepLevel2 = $salesRepLevel2;

        return $this;
    }

    /**
     * @param string $salesRepLevel3
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel3($salesRepLevel3)
    {
        $this->salesRepLevel3 = $salesRepLevel3;

        return $this;
    }

    /**
     * @param string $salesRepLevel4
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel4($salesRepLevel4)
    {
        $this->salesRepLevel4 = $salesRepLevel4;

        return $this;
    }

    /**
     * @param string $salesRepLevel5
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel5($salesRepLevel5)
    {
        $this->salesRepLevel5 = $salesRepLevel5;

        return $this;
    }

    /**
     * @param string $salesRepLevel6
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel6($salesRepLevel6)
    {
        $this->salesRepLevel6 = $salesRepLevel6;

        return $this;
    }

    /**
     * @param string $salesRepLevel7
     * @return DwhDimGeoSalesRep
     */
    public function setSalesRepLevel7($salesRepLevel7)
    {
        $this->salesRepLevel7 = $salesRepLevel7;

        return $this;
    }


}
