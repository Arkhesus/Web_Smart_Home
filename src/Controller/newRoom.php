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

        $form = $this->createFormsNew($room, $request);

        $formDelete = $this->createFormsDelete($room, $request);

        if($formDelete->isSubmitted() && $formDelete->isValid()) {
            return $this->redirectToRoute("menu");
        }else if($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute("menu");
        }else{
            return $this->render('newRoom.twig', [
                "formRoom" => $form->createView(),
                "formDelete" => $formDelete->createView()
            ]);
        }

    }
    

    public function createFormsNew(Room $room, Request $request) {
        
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

    public function createFormsDelete(Room $room, Request $request) {

        $sensor = new Sensors();
        
        $form = $this->createFormBuilder($sensor)
            ->add('Room' , EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'Name',
                "choice_value" => 'Name',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Delete '
            ])
            ->getForm();

        $form ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $this->DeleteRoom($sensor->getRoom());
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

    public function DeleteRoom(Room $room)
    {
        if($room->getName() != "None"){
            
            $entityManager = $this->getDoctrine()->getManager();
            $product = $entityManager->getRepository(Sensors::class)->findBy(["Room" => $room]);
            dump($product);

            $none = $entityManager->getRepository(Category::class)->findBy(["Name" => "None"]);
            dump($none);

            foreach($product as $p){
                $p->setRoom($none[0]);
                $entityManager->persist($p);
            }

            $entityManager->remove($room);
            $entityManager->flush();
        
        }
      
    }



}

?>