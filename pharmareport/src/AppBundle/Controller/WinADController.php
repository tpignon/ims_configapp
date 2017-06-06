<?php
// src/AppBundle/Controller/WinADController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

//use Symfony\Component\Ldap\LdapClient;
use Symfony\Component\Ldap\Ldap;

class WinADController
{
    /**
     * @Route("/winad/connect")
     */
    public function connectAction()
    {
      // Without using the service ldap and the global security module 
      $res = "null";

      // Eléments d'authentification LDAP
      $ldaprdn  = 'INTERNAL\MPignon';     // DN ou RDN LDAP
      $ldappass = 'FOR:pitip123';  // Mot de passe associé

      // Connexion au serveur LDAP
      $ldapconn = ldap_connect("internal.imsglobal.com")
          or die("Impossible de se connecter au serveur LDAP.");

      if ($ldapconn) {
          // Connexion au serveur LDAP
          $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);

          // Vérification de l'authentification
          if ($ldapbind) {
              $res = "passed";
          } else {
            $res = "failed";
          }

      }

        return new Response(
            "<html><body>Connection $res.</body></html>"
        );
    }
}
?>
