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
use PhpParser\Node\Name;

class Update extends AbstractController
{

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

        $form = $this->createForms($sensor, $request, $name);

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

    public function createForms(Sensors $sensor, Request $request, string $name) {
        
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