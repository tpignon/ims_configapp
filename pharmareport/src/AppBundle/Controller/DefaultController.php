<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/admin")
     */
    public function adminAction()
    {
        return new Response('<html><body>Admin page!</body></html>');
    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
      // Test Connection (Optional)
      try {
          $entityManager =  $this->getDoctrine()->getManager();
          $entityManager->getConnection()->connect();
      } catch (\Exception $e) {
          // failed to connect
          return new Response('<html><body>Connection failed !<br>'.$e.'</body></html>');
      }

      // Get GeoSalesRep mappings
      $geoSalesRepMappings = $this->getDoctrine()
         ->getRepository('AppBundle:GeoSalesRep')
         ->findAll();

      if (!$geoSalesRepMappings) {
         throw $this->createNotFoundException(
             'No geoSalesRepMapping found.'
         );
       }
        return new Response('<html><body>Loaded '.count($geoSalesRepMappings).' geoSalesRepMappings !</body></html>');
    }
}
