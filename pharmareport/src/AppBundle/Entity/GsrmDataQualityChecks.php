<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeoSalesRepDataQualityChecks
 *
 * @ORM\Table(name="gsrm_data_quality_checks")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GsrmDataQualityChecksRepository")
 */
class GsrmDataQualityChecks
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
     * @ORM\Column(name="load_date", type="string", length=255, nullable=true)
     */
    private $loadDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=5000, nullable=true)
     */
    private $info;


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
    public function getLoadDate()
    {
        return $this->loadDate;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param integer $clientOutputId
     * @return \AppBundle\Entity\GsrmDataQualityChecks
     */
    public function setClientOutputId($clientOutputId)
    {
        $this->clientOutputId = $clientOutputId;

        return $this;
    }

    /**
     * @param string $loadDate
     * @return \AppBundle\Entity\GsrmDataQualityChecks
     */
    public function setLoadDate($loadDate)
    {
        $this->loadDate = $loadDate;

        return $this;
    }

    /**
     * @param string $status
     * @return \AppBundle\Entity\GsrmDataQualityChecks
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string $info
     * @return \AppBundle\Entity\GsrmDataQualityChecks
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }
}
