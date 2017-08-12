<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class GsrmExportMappingTemplateFile
{

      /**
       * @Assert\NotBlank(message="Please select a Dataset.")
       */
      private $datasetName;

      public function getDatasetName()
      {
          return $this->datasetName;
      }

      public function setDatasetName($datasetName)
      {
          $this->datasetName = $datasetName;
          return $this;
      }

}
