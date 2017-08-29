<?php

namespace AppBundle\Entity;

//use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class TarExportTargetsTemplateFile
{
      /**
       * @Assert\NotBlank(message="Please select a Dataset.")
       */
      private $dataset;

      public function getDataset()
      {
          return $this->dataset;
      }

      public function setDataset($dataset)
      {
          $this->dataset = $dataset;
          return $this;
      }

}
