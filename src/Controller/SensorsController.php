<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

use App\Controller\CategoryController;
use App\Controller\RoomController;

use App\Repository\CategoryRepository;
use App\Repository\RoomRepository;
use App\Repository\SensorsRepository;

class SensorsController extends AbstractController
{


    public function list(Request $request)
    {
        $sensor = New Sensors(); 
        list($form, $cat, $room) = $this->createFormsFilter($sensor, $request);




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


    public function createFormsFilter(Sensors $sensor, Request $request) {


        
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

    public function DeleteSensor($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Sensors::class)->find($id);


        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $cat = New Category();
        $room = New Room();
        $cat->setName("");
        $room->setName("");

        $product->setCategory($cat);
        $product->setRoom($room);

        dump($product);

        $entityManager->persist($product);
        $entityManager->remove($product);
        $entityManager->flush();  


        return $this->redirectToRoute("menu");
        
    }

    public function AddSensor(Request $request)
    {

        $sensor = new Sensors();

        $form = $this->createForms($sensor, $request);

        if($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute("menu");
        }else{
            return $this->render('newSensor.twig', [
                'formNewSensor' => $form->createView()
            ]);
        }

        
    }

    public function createForms(Sensors $sensor, Request $request) {

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        
        $form = $this->createFormBuilder($sensor)
            ->add('Name' , TextType::class)
            ->add('Category' , EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'Name',
                "choice_value" => 'Name',
                'required' => true,
            ])
            ->add('Room' , EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'Name',
                "choice_value" => 'Name',
                'required' => true,
            ])
            ->getForm();

        dump($sensor);
        dump($form);
        dump($request);

        $form ->handleRequest($request);


      
        dump($sensor);
        if($form->isSubmitted() && $form->isValid()){
            $this->CreateSensor($sensor->getName(), $sensor->getCategory(), $sensor->getRoom());
        }


        return $form;
    }


    public function CreateSensor(string $name, Category $category, Room $room)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $sensor = new Sensors();
        $sensor->setName($name);
        $sensor->setCategory($category);
        $sensor->setRoom($room);

        $entityManager->persist($sensor);
        $entityManager->flush();
    }

    public function display($name, Request $request){


        $sensor = $this->getDoctrine()->getRepository(Sensors::class)->findOneBy(['Name' => $name]);
        
        if (!$sensor) {
            throw $this->createNotFoundException(
                'No product found for id '.$name
            );
        }

        $form = $this->createFormsUpdate($sensor, $request, $name);

        if($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute("menu");
        }else{
            return $this->render('update.twig', [
                "sensor" => $sensor,
                'formUpdate' => $form->createView()
            ]);
        }

        
    }
    

    public function AddCategory(string $name, Category $newCategory){
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Sensors::class)->findBy(["Name" => $name]);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for name'.$name
            );
        }

        foreach($product as $sensor){
            $sensor->setCategory($newCategory);
            $entityManager->flush();
        }

    }

    public function createFormsUpdate(Sensors $sensor, Request $request, string $name) {
        
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
            ->add('save', SubmitType::class, [
                'label' => 'Update ' .$name
            ])
            ->getForm();

        $form ->handleRequest($request);

      
        dump($sensor);

        if($form->isSubmitted() && $form->isValid()){
            $this->AddRoom($sensor->getName(), $sensor->getRoom());
            $this->AddCategory($sensor->getName(), $sensor->getCategory());
        }


        return $form;
    }

    public function AddRoom(string $name, Room $newRoom){
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Sensors::class)->findBy(["Name" => $name]);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for name'.$name
            );
        }

        foreach($product as $sensor){
            $sensor->setRoom($newRoom);
            $entityManager->flush();
        }


    }



    public function DeleteCategory(Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Category::class)->findBy(["Name" => $category]);


        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$category
            );
        }

        $entityManager->remove($product[0]);

        $sensors = $entityManager->getRepository(Sensors::class)->findBy(["Category" => $category]);
        $entityManager->flush();

        foreach($sensors as $sensor)
        {
            print($sensor->getName());
            $this->AddCategory($sensor->getName(), "" );

        }

        
    }

    

    public function CreateCategory(string $name)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $category = new Category();
        $category->setName($name);

        $entityManager->persist($category);
        $entityManager->flush();
    }

    /**
    * @Route("api/filter/{category?}/{room?}", name="api_get_filter", methods={"GET"})
    */
    public function APIfilter($category, $room, Request $request,SensorsRepository $SensorsRepository, SerializerInterface $serializer, ValidatorInterface $validator) {

        if($room != 'null'){
            $entityManager = $this->getDoctrine()->getManager();
            $get_room = $entityManager->getRepository(Room::class)->findBy(["Name" => $room])[0];
            $entityManager->flush();

            $get_category = new Category();


        }

        if($category != 'null'){
  
            $entityManager = $this->getDoctrine()->getManager();
            $get_category = $entityManager->getRepository(Category::class)->findBy(["Name" => $category])[0];
            $entityManager->flush();
            $get_room = new Room();
        }

        if($category != 'null' && $room != 'null'){
            $entityManager = $this->getDoctrine()->getManager();
            $get_category = $entityManager->getRepository(Category::class)->findBy(["Name" => $category])[0];
            $entityManager->flush();

            $entityManager = $this->getDoctrine()->getManager();
            $get_room = $entityManager->getRepository(Room::class)->findBy(["Name" => $room])[0];
            $entityManager->flush();
        }
        else {
            $get_room = new Room();
            $get_category = new Category();
        }



        $sensor = $this->getSensor($get_category, $get_room);


        
        return $this->json($sensor, 200, []);
    }

    /**
    * @Route("api/sensor/{name?}", name="api_get_sensor", methods={"GET"})
    */
    public function APIget($name, Request $request,SensorsRepository $SensorsRepository, SerializerInterface $serializer, ValidatorInterface $validator) {

        try{
        if($name){
            $entityManager = $this->getDoctrine()->getManager();
            $sensor = $entityManager->getRepository(Sensors::class)->findBy(["Name" => $name]);
            $entityManager->flush();


            return $this->json($sensor, 200, []);

        }else {
            $cat = new Category();
            $room = new Room();
            $sensor = $this->getSensor($cat, $room);
            return $this->json($sensor, 200, []);
        }

        }catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
    }
        

        
       }


    /**
    * @Route("api/sensor/{name?}", name="api_option_sensor", methods={"OPTIONS"})
    */
    public function APIoption($name, Request $request,SensorsRepository $SensorsRepository, SerializerInterface $serializer, ValidatorInterface $validator) {

            
            return $this->json([], 200, []);


        
       }


       

    /**
    * @Route("api/sensor", name="api_post_sensor", methods={"POST"})
    */
    public function API_Post(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
    $receivedJson = $request->getContent();

    try {
        
        $get = $serializer->deserialize($receivedJson, Sensors::class, 'json');
        
        $errors = $validator->validate($get, null, ["post"]);

        $entityManager = $this->getDoctrine()->getManager();
        $cat = $entityManager->getRepository(Category::class)->findBy(["Name" => $get->getCategory()->getName()]);
        $entityManager->flush();

        $entityManager = $this->getDoctrine()->getManager();
        $room = $entityManager->getRepository(Room::class)->findBy(["Name" => $get->getRoom()->getName()]);
        $entityManager->flush();

        $get->setCategory($cat[0]);
        $get->setRoom($room[0]);


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
    * @Route("api/sensor", name="api_delete_sensor", methods={"DELETE"})
    */
    public function API_Delete(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        
        $receivedJson = $request->getContent();
        
        try {
            $get = $serializer->deserialize($receivedJson, Category::class, 'json');

            $entityManager = $this->getDoctrine()->getManager();
            $cat = $entityManager->getRepository(Sensors::class)->findBy(["Name" => $get->getName()]);
            
            
            $errors = $validator->validate($get, null, ["delete"]);

            if(count($errors) > 0 ) {
                return $this->json($errors, 400);
            }

            
            $this->DeleteSensor($cat[0]->getId());
            
            return $this->json($cat[0], 201, []);

        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }

    /**
    * @Route("/api/sensor", name="api_put_sensor", methods={"PUT"})
    */
    public function API_Put(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        
        $json = $request->getContent();

        // $string = str_replace('\n', '', $receivedJson);
        // $string = rtrim($string, ',');
        // $string = "[" . trim($string) . "]";
        $json = json_decode($json, true);

        // dump($json);

        // $receivedJson["id"] = (int)$receivedJson["id"];
        try{

            
        $entityManager = $this->getDoctrine()->getManager();
        
        $sensor = $entityManager->getRepository(Sensors::class)->findBy(["id" => $json["id"]]);
        
        $cat = $entityManager->getRepository(Category::class)->findBy(["id" => $json["Category"]["id"]]);
        $room = $entityManager->getRepository(Room::class)->findBy(["id" => $json["Room"]["id"]]);
        
        $sensor[0]->setName($json["name"]);
        $sensor[0]->setCategory($cat[0]);
        $sensor[0]->setRoom($room[0]);

        $entityManager->persist($sensor[0]);
        $entityManager->flush();

        return $this->json($sensor[0], 201, []);
        
        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

}

?>