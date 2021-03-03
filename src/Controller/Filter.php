<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

class Filter extends AbstractController
{

    /**
     * @Route("/test", name="test_list")
     */
    public function list(Request $request)
    {
        $sensor = New Sensors(); 
        list($form, $cat, $room) = $this->createForms($sensor, $request);




        if($form->isSubmitted() && $form->isValid()){
            $sensors = $this->getSensor($cat, $room);
            dump("Bonjour");
        }else{
            $sensors = $this->getDoctrine()->getRepository(Sensors::class)->findAll();
        }
        
        

        return $this->render('menu.twig', [
            "sensors" => $sensors,
            'formFilter' => $form->createView()
        ]);

        
    }

    public function getSensor(Category $cat, Room $room)
    {
        $TestCat = $cat->getName();
        $TestRoom = $room->getName();

        if(!$TestCat && !$TestRoom){
            $sensors = $this->getDoctrine()->getRepository(Sensors::class)->findAll();
        }elseif(!$TestCat){
            $sensors = $this->getDoctrine()->getRepository(Sensors::class)->findBy([
                "Room" =>  $room
            ]);
        }elseif(!$TestRoom){
            $sensors = $this->getDoctrine()->getRepository(Sensors::class)->findBy([
                "Category" => $cat
            ]);
        }else{
            $sensors = $this->getDoctrine()->getRepository(Sensors::class)->findBy([
                "Category" => $cat,
                "Room" =>  $room
            ]);
        }
        return $sensors;
        

        
    }

    public function DeleteSensor(int $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Sensors::class)->find($id);


        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($product);
        $entityManager->flush();  
    }

    public function createForms(Sensors $sensor, Request $request) {


        
        $form = $this->createFormBuilder($sensor)
            ->add('Category' , EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'Name',
                "choice_value" => 'Name',
                'required' => false,
            ])
            ->add('Room' , EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'Name',
                "choice_value" => 'Name',
                'required' => false,
            ])
            ->getForm();

        dump($sensor);


        $form ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            // if()
            $cat =  $sensor->getCategory();
            $room = $sensor->getRoom();

            if(!$sensor->getCategory()){
                $cat = new Category();
            }
            if(!$sensor->getRoom()){
                $room = new Room();
            }
        }else{
            return array($form, new Category(), new Room());
        }


        return array($form,$cat, $room  );
    }


}

?>