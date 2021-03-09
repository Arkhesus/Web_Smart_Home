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
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

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

        // $this->AddCategory(1, "Lumiere");
        // $this->AddRoom(2, "Laura's bedroom");
        // $this->DeleteRoom("Guillaume's Bedroom");
        //$this->DeleteCategory("Alarm");
        // $this->DeleteSensor(2);
        // $this->CreateCategory("Alarm");
        // $this->CreateRoom("Guillaume's Bedroom");
        // $this->CreateSensor("Security Alarm", "Alarm", "Guillaume's Bedroom");

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



}

?>