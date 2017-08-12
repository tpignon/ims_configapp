<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* GsrmCurrentMapping
*
* @ORM\Table(name="gsrm_current_mapping")
* @ORM\Entity(repositoryClass="AppBundle\Repository\GsrmCurrentMappingRepository")
*/
class GsrmCurrentMapping
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
     * @ORM\Column(name="version_geo_structure_code", type="string", length=50)
     */
    private $versionGeoStructureCode;
    /**
     * @ORM\Column(name="geo_team", type="string", length=150)
     */
    private $geoTeam;
    /**
     * @ORM\Column(name="geo_level_number", type="integer")
     */
    private $geoLevelNumber;
    /**
     * @ORM\Column(name="geo_value", type="string", length=150)
     */
    private $geoValue;
    /**
     * @ORM\Column(name="sr_first_name", type="string", length=255)
     */
    private $srFirstName;
    /**
     * @ORM\Column(name="sr_last_name", type="string", length=255)
     */
    private $srLastName;
    /**
     * @ORM\Column(name="sr_email", type="string", length=255)
     */
    private $srEmail;

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
     * Set clientOutputId
     *
     * @param integer $clientOutputId
     *
     * @return GeoSalesRep
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
     * Set versionGeoStructureCode
     *
     * @param string $versionGeoStructureCode
     *
     * @return GeoSalesRep
     */
    public function setVersionGeoStructureCode($versionGeoStructureCode)
    {
        $this->versionGeoStructureCode = $versionGeoStructureCode;

        return $this;
    }

    /**
     * Get versionGeoStructureCode
     *
     * @return string
     */
    public function getVersionGeoStructureCode()
    {
        return $this->versionGeoStructureCode;
    }

    /**
     * Set geoTeam
     *
     * @param string $geoTeam
     *
     * @return GeoSalesRep
     */
    public function setGeoTeam($geoTeam)
    {
        $this->geoTeam = $geoTeam;

        return $this;
    }

    /**
     * Get geoTeam
     *
     * @return string
     */
    public function getGeoTeam()
    {
        return $this->geoTeam;
    }

    /**
     * Set geoLevelNumber
     *
     * @param integer $geoLevelNumber
     *
     * @return GeoSalesRep
     */
    public function setGeoLevelNumber($geoLevelNumber)
    {
        $this->geoLevelNumber = $geoLevelNumber;

        return $this;
    }

    /**
     * Get geoLevelNumber
     *
     * @return integer
     */
    public function getGeoLevelNumber()
    {
        return $this->geoLevelNumber;
    }

    /**
     * Set geoValue
     *
     * @param string $geoValue
     *
     * @return GeoSalesRep
     */
    public function setGeoValue($geoValue)
    {
        $this->geoValue = $geoValue;

        return $this;
    }

    /**
     * Get geoValue
     *
     * @return string
     */
    public function getGeoValue()
    {
        return $this->geoValue;
    }

    /**
     * Set srFirstName
     *
     * @param string $srFirstName
     *
     * @return GeoSalesRep
     */
    public function setSrFirstName($srFirstName)
    {
        $this->srFirstName = $srFirstName;

        return $this;
    }

    /**
     * Get srFirstName
     *
     * @return string
     */
    public function getSrFirstName()
    {
        return $this->srFirstName;
    }

    /**
     * Set srLastName
     *
     * @param string $srLastName
     *
     * @return GeoSalesRep
     */
    public function setSrLastName($srLastName)
    {
        $this->srLastName = $srLastName;

        return $this;
    }

    /**
     * Get srLastName
     *
     * @return string
     */
    public function getSrLastName()
    {
        return $this->srLastName;
    }

    /**
     * Set srEmail
     *
     * @param string $srEmail
     *
     * @return GeoSalesRep
     */
    public function setSrEmail($srEmail)
    {
        $this->srEmail = $srEmail;

        return $this;
    }

    /**
     * Get srEmail
     *
     * @return string
     */
    public function getSrEmail()
    {
        return $this->srEmail;
    }

    public function toArray()
    {
        return array(
            $this->clientOutputId,
            $this->versionGeoStructureCode,
            $this->geoTeam,
            $this->geoLevelNumber,
            $this->geoValue,
            $this->srFirstName,
            $this->srLastName,
            $this->srEmail
        );
    }

}
