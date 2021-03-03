<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

class newRoom extends AbstractController
{

    public function AddRoom(Request $request){

        $room = New Room();

        $form = $this->createForms($room, $request);

        if($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute("menu");
        }else{
            return $this->render('newRoom.twig', [
                "formRoom" => $form->createView()
            ]);
        }
    }
    

    public function createForms(Room $room, Request $request) {
        
        $form = $this->createFormBuilder($room)
            ->add('Name' , TextType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Create '
            ])
            ->getForm();

        $form ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $this->CreateRoom($room->getName());
        }


        return $form;
    }

    public function CreateRoom(string $name)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $room = new Room();
        $room->setName($name);

        $entityManager->persist($room);
        $entityManager->flush();
    }



}

?>