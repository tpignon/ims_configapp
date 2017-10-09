<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* Dataset
*
* @ORM\Table(name="datasets")
* @ORM\Entity(repositoryClass="AppBundle\Repository\DatasetRepository")
*/
class Dataset
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
     * @ORM\Column(name="client_name", type="string", length=255)
     */
    private $clientName;
    /**
     * @ORM\Column(name="study", type="string", length=255)
     */
    private $study;

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
     * Get clientName
     *
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Get study
     *
     * @return string
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * Set clientOutputId
     *
     * @param integer $clientOutputId
     *
     * @return Dataset
     */
    public function setClientOutputId($clientOutputId)
    {
        $this->clientOutputId = $clientOutputId;

        return $this;
    }

    /**
     * Set clientName
     *
     * @param string $clientName
     *
     * @return Dataset
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Set study
     *
     * @param string $study
     *
     * @return Dataset
     */
    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

}
