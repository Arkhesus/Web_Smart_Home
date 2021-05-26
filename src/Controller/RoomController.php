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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

use App\Repository\CategoryRepository;
use App\Repository\RoomRepository;
use App\Repository\SensorsRepository;

class RoomController extends AbstractController
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

    /**
    * @Route("/api/room/{name?}", name="api_get_room", methods={"GET"})
    */
    public function APIget($name, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, RoomRepository $RoomRepository) {

        try{
            if($name){
                $entityManager = $this->getDoctrine()->getManager();
                $sensor = $entityManager->getRepository(Room::class)->findBy(["Name" => $name]);
                $entityManager->flush();
    
    
                return $this->json($sensor, 201, []);
    
            }else {
                
                return $this->json($RoomRepository->findAll(), 200, []);
            }
    
            }catch(NotEncodableValueException $e){
                return $this->json([
                    'status' => 400,
                    'message' => $e->getMessage()
                ], 400);
        }

        
       }

    /**
    * @Route("/api/room", name="api_option_room", methods={"OPTIONS"})
    */
    public function APIoption() {

            
        return $this->json([], 200, []);


    
   }

    /**
    * @Route("/api/room", name="api_post_room", methods={"POST"})
    */
    public function API_Post(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        $receivedJson = $request->getContent();

        try {
            
            $get = $serializer->deserialize($receivedJson, Room::class, 'json');
            
            $errors = $validator->validate($get, null, ["post"]);

            if(count($errors) > 0 ) {
                return $this->json($errors, 400);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($get);
            $entityManager->flush();
            
            return $this->json($get, 201, []);

        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }        

    }

    /**
    * @Route("/api/room", name="api_delete_room", methods={"DELETE"})
    */
    public function API_Delete(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        
        $receivedJson = $request->getContent();
        
        try {
            $get = $serializer->deserialize($receivedJson, Room::class, 'json');

            $entityManager = $this->getDoctrine()->getManager();
            $room = $entityManager->getRepository(Room::class)->findBy(["Name" => $get->getName()]);
            
            
            $errors = $validator->validate($get, null, ["delete"]);

            if(count($errors) > 0 ) {
                return $this->json($errors, 400);
            }

            
            $this->DeleteRoom($room[0]);
            
            return $this->json($room[0], 201, []);

        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }


    /**
    * @Route("/api/room", name="api_put_room", methods={"PUT"})
    */
    public function API_Put(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        
        $receivedJson = $request->getContent();

        $string = str_replace('\n', '', $receivedJson);
        $string = rtrim($string, ',');
        $string = "[" . trim($string) . "]";
        $json = json_decode($string, true);

        // $receivedJson["id"] = (int)$receivedJson["id"];
        try{

            
        $entityManager = $this->getDoctrine()->getManager();
        $room = $entityManager->getRepository(Room::class)->findBy(["id" => $json[0]["id"]]);
        
        

        $room[0]->setName($json[0]["Name"]);

        $entityManager->persist($room[0]);
        $entityManager->flush();

        return $this->json($room[0], 201, []);
        
        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }



}

?>