<?PHP

// src/Controller/Test.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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

class CategoryController extends AbstractController
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

    /**
    * @Route("api/category/{name?}", name="api_get_category", methods={"GET"})
    */
    public function APIgetAll($name, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, CategoryRepository $CategoryRepository) {
        try{
            if($name){
                $entityManager = $this->getDoctrine()->getManager();
                $sensor = $entityManager->getRepository(Category::class)->findBy(["Name" => $name]);
                $entityManager->flush();
    
    
                return $this->json($sensor, 201, []);
    
            }else {
                
                return $this->json($CategoryRepository->findAll(), 200, []);
            }
    
            }catch(NotEncodableValueException $e){
                return $this->json([
                    'status' => 400,
                    'message' => $e->getMessage()
                ], 400);
        }

        
       }

    /**
    * @Route("api/category", name="api_option_category", methods={"OPTIONS"})
    */
    public function APIoption() {

        return $this->json([], 200, []);

    
   }

    /**
    * @Route("api/category", name="api_post_category", methods={"POST"})
    */
    public function API_Post(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        $receivedJson = $request->getContent();

        try {
            
            $get = $serializer->deserialize($receivedJson, Category::class, 'json');
            
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
    * @Route("api/category", name="api_delete_category", methods={"DELETE"})
    */
    public function API_Delete(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        
        $receivedJson = $request->getContent();
        
        try {
            $get = $serializer->deserialize($receivedJson, Category::class, 'json');

            $entityManager = $this->getDoctrine()->getManager();
            $cat = $entityManager->getRepository(Category::class)->findBy(["Name" => $get->getName()]);
            
            
            $errors = $validator->validate($get, null, ["delete"]);

            if(count($errors) > 0 ) {
                return $this->json($errors, 400);
            }

            
            $this->DeleteCategory($cat[0]);
            
            return $this->json($cat[0], 201, []);

        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }

    /**
    * @Route("api/category", name="api_put_category", methods={"PUT"})
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
        $cat = $entityManager->getRepository(Category::class)->findBy(["id" => $json[0]["id"]]);
        
        

        $cat[0]->setName($json[0]["Name"]);

        $entityManager->persist($cat[0]);
        $entityManager->flush();

        return $this->json($cat[0], 201, []);
        
        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }





}

?>