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

class newCategory extends AbstractController
{

    public function AddCategory(Request $request){

        $category = New Category();

        $form = $this->createForms($category, $request);

        $formDelete = $this->createFormsDelete($category, $request);

        if($formDelete->isSubmitted() && $formDelete->isValid()) {
            return $this->redirectToRoute("menu");
        }else if($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute("menu");
        }else{
            return $this->render('newCategory.twig', [
                "formCategory" => $form->createView(),
                "formDelete" => $formDelete->createView()
            ]);
        }
    }
    

    public function createForms(Category $category, Request $request) {
        
        $form = $this->createFormBuilder($category)
            ->add('Name' , TextType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Create '
            ])
            ->getForm();

        $form ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $this->CreateCategory($category->getName());
            
        }


        return $form;
    }

    public function createFormsDelete(Category $category, Request $request) {

        $sensor = new Sensors();
        
        $form = $this->createFormBuilder($sensor)
            ->add('Category' , EntityType::class, [
                'class' => Category::class,
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
            $this->DeleteCategory($sensor->getCategory());
        }


        return $form;
    }

    public function CreateCategory(string $name)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $category = new Category();
        $category->setName($name);

        $entityManager->persist($category);
        $entityManager->flush();
    }

    public function DeleteCategory(Category $category){


        if($category->getName() != "None"){
            
            $entityManager = $this->getDoctrine()->getManager();
            $product = $entityManager->getRepository(Sensors::class)->findBy(["Category" => $category]);
            dump($product);

            $none = $entityManager->getRepository(Category::class)->findBy(["Name" => "None"]);
            dump($none);

            foreach($product as $p){
                $p->setCategory($none[0]);
                $entityManager->persist($p);
            }

            $entityManager->remove($category);
            $entityManager->flush();
        
        }
        
    }



}

?>