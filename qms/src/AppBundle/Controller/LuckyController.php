<?php
// src/AppBundle/Controller/LuckyController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class LuckyController
{
    /**
     * @Route("/lucky/number")
     */
    public function numberAction()
    {
        // SECURITY: The second parameter is used to specify on what object the role is tested.
        $this->denyAccessUnlessGranted('ROLE_USER', $this, 'Unable to access this page!');

        $number = mt_rand(0, 100);

        return new Response(
            '<html><body>Lucky number 1: '.$number.'</body></html>'
        );
    }

    /**
     * @Route("/lucky/number2")
     * @Security("has_role('ROLE_USER')")
     */
    public function number2Action()
    {
        $number = mt_rand(0, 100);

        return new Response(
            '<html><body>Lucky number 2: '.$number.'</body></html>'
        );
    }
}
?>
