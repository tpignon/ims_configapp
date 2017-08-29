<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* GsrmImportMapping
*
* @ORM\Table(name="gsrm_import_mapping")
* @ORM\Entity(repositoryClass="AppBundle\Repository\GsrmImportMappingRepository")
*/
class GsrmImportMapping
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
     * @ORM\Column(name="version_geo_structure_code", type="string", length=50)
     * @Assert\NotBlank(message="Column Version geo structure code should not be blank.")
     * @Assert\Type("string", message="The VersionGeoStructureCode {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(max=50, maxMessage="The VersionGeoStructureCode {{ value }} is too long. It is limited to 50 characters.")
     */
    private $versionGeoStructureCode;

    /**
     * @ORM\Column(name="geo_team", type="string", length=150)
     * @Assert\Type("string", message="The VersionGeoStructureCode {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(max=150, maxMessage="The MarketID (GeoTeam) {{ value }} is too long. It is limited to 150 characters.")
     */
    private $geoTeam;

    /**
     * @ORM\Column(name="geo_level_number", type="integer")
     * @Assert\NotBlank(message="Column Level (Geo level number) should not be blank.")
     * @Assert\Choice(choices = {
     *    "",
     *    "1",
     *    "2",
     *    "3",
     *    "4",
     *    "5",
     *    "6",
     *    "7"},
     *    message = "The Level (GeoLevelNumber) {{ value }} is not valid. This value should be between 1 and 7.")
     */
    private $geoLevelNumber;

    /**
     * @ORM\Column(name="geo_value", type="string", length=150)
     * @Assert\NotBlank(message="Column NameLevel (Geo value) should not be blank.")
     * @Assert\Type("string", message="The VersionGeoStructureCode {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(max=150, maxMessage="The NameLevel (GeoValue) {{ value }} is too long. It is limited to 150 characters.")
     */
    private $geoValue;

    /**
     * @ORM\Column(name="sr_first_name", type="string", length=255)
     * @Assert\Type("string", message="The VersionGeoStructureCode {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(max=255, maxMessage="The First Name {{ value }} is too long. It is limited to 255 characters.")
     */
    private $srFirstName;

    /**
     * @ORM\Column(name="sr_last_name", type="string", length=255)
     * @Assert\Type("string", message="The VersionGeoStructureCode {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(max=255, maxMessage="The Last Name {{ value }} is too long. It is limited to 255 characters.")
     */
    private $srLastName;

    /**
     * @ORM\Column(name="sr_email", type="string", length=255)
     * @Assert\Type("string", message="The VersionGeoStructureCode {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(max=255, maxMessage="The Email {{ value }} is too long. It is limited to 255 characters.")
     */
    private $srEmail;

    /**
     * @ORM\Column(name="mapping_status", type="string", length=255, nullable=true)
     */
    private $mappingStatus;



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
     * Get versionGeoStructureCode
     *
     * @return string
     */
    public function getVersionGeoStructureCode()
    {
        return $this->versionGeoStructureCode;
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
     * Get geoLevelNumber
     *
     * @return integer
     */
    public function getGeoLevelNumber()
    {
        return $this->geoLevelNumber;
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
     * Get srFirstName
     *
     * @return string
     */
    public function getSrFirstName()
    {
        return $this->srFirstName;
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
     * Get srEmail
     *
     * @return string
     */
    public function getSrEmail()
    {
        return $this->srEmail;
    }

    /**
     * Get mappingStatus
     *
     * @return string
     */
    public function getMappingStatus()
    {
        return $this->mappingStatus;
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

        //return $this;
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

        //return $this;
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

        //return $this;
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

        //return $this;
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

        //return $this;
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

        //return $this;
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

        //return $this;
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

        //return $this;
    }

    /**
     * Set mappingStatus
     *
     * @param string $mappingStatus
     *
     * @return GeoSalesRep
     */
    public function setMappingStatus($mappingStatus)
    {
        $this->mappingStatus = $mappingStatus;

        return $this;
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
