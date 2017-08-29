<?php

namespace AppBundle\Entity;

//use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class TarImportTargetsFile
{

      /**
       * @Assert\File(
       *    maxSize = "10M",
       *    mimeTypes = {"text/csv", "text/plain"},
       *    mimeTypesMessage="The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}. Please upload a valid CSV.",
       *    maxSizeMessage = "The file is too large {{ size }} {{ suffix }. Allowed maximum size is {{ limit }} {{ suffix }}."
       *    )
       */
      private $importTargetsFile;

      public function getImportTargetsFile()
      {
          return $this->importTargetsFile;
      }

      public function setImportTargetsFile($importTargetsFile)
      {
          $this->importTargetsFile = $importTargetsFile;
          return $this;
      }

}
