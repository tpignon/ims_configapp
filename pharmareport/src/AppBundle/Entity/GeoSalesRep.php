<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="l_ph_geo_sales_rep")
*/
class GeoSalesRep
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ptk;
    /**
     * @ORM\Column(type="integer")
     */
    private $clientOutputId;
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $versionGeoStructureCode;
    /**
     * @ORM\Column(type="string", length=150)
     */
    private $geoTeam;
    /**
     * @ORM\Column(type="integer")
     */
    private $geoLevelNumber;
    /**
     * @ORM\Column(type="string", length=150)
     */
    private $geoValue;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $srFirstName;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $srLastName;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $srEmail;
}

 ?>
