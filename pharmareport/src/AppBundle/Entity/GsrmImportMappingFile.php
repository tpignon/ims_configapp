<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class GsrmImportMappingFile
{

      /**
       * @Assert\File(
       *    maxSize = "10M",
       *    mimeTypes = {"text/csv", "text/plain"},
       *    mimeTypesMessage="The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}. Please upload a valid CSV.",
       *    maxSizeMessage = "The file is too large {{ size }} {{ suffix }. Allowed maximum size is {{ limit }} {{ suffix }}."
       *    )
       */
      private $gsrmImportMappingFile;

      public function getGsrmImportMappingFile()
      {
          return $this->gsrmImportMappingFile;
      }

      public function setGsrmImportMappingFile($gsrmImportMappingFile)
      {
          $this->gsrmImportMappingFile = $gsrmImportMappingFile;
          return $this;
      }



}
