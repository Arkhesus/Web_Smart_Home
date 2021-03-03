<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Entity\Room;
use App\Entity\Sensors;


class newSensor extends AbstractController
{
    /**
     * @Route("/test", name="test_list")
     */
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

} 