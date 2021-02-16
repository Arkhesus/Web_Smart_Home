<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;

class Update extends AbstractController
{

    public function display($name){

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
                'No product found for id '.$id
            );
        }


        return $this->render('update.twig', [
            "sensor" => $sensor,
        ]);
    }

    public function AddCategory(string $name, string $newCategory){
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

    public function AddRoom(string $name, string $newRoom){
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

    public function DeleteRoom(string $room)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Room::class)->findBy(["Name" => $room]);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$room
            );
        }

        $entityManager->remove($product[0]);
        $sensors = $entityManager->getRepository(Sensors::class)->findBy(["Room" => $room]);
        $entityManager->flush();

        foreach($sensors as $sensor)
        {
            $this->AddRoom($sensor->getName(), "" );
        }
    }

    public function DeleteCategory(string $category)
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

    public function CreateCategory(string $name)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $category = new Category();
        $category->setName($name);

        $entityManager->persist($category);
        $entityManager->flush();
    }

    public function CreateRoom(string $name)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $room = new Room();
        $room->setName($name);

        $entityManager->persist($room);
        $entityManager->flush();
    }

    public function CreateSensor(string $name, string $category, string $room)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $sensor = new Sensors();
        $sensor->setName($name);
        $sensor->setCategory($category);
        $sensor->setRoom($room);

        $entityManager->persist($sensor);
        $entityManager->flush();
    }


}

?>