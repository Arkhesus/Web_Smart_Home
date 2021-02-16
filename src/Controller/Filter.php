<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

class Filter extends AbstractController
{
    /**
     * @Route("/test", name="test_list")
     */
    public function list()
    {

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();


        $rooms = $this->getDoctrine()->getRepository(Room::class)->findAll();

        
        $sensors = $this->getSensor();

        return $this->render('menu.twig', [
            "categories" => $categories,
            "rooms" => $rooms,
            "sensors" => $sensors
        ]);

        
    }

    public function getSensor()
    {
        $sensors = $this->getDoctrine()->getRepository(Sensors::class)->findAll();

        return $sensors;
    }
}

?>